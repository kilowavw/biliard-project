<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use App\Models\Penyewaan;
use App\Models\HargaSetting;
use App\Models\Kupon; // Import model Kupon
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KasirController extends Controller
{
    public function dashboard()
    {
        $mejas = Meja::all();
        // Konsistenkan status penyewaan aktif menjadi 'berlangsung'
        $penyewaanAktif = Penyewaan::where('status', 'berlangsung')->get();

        return view('kasir.dashboard', [
            'mejas' => $mejas,
            'penyewaanAktif' => $penyewaanAktif,
            'serverTime' => now()->toIso8601String(), // Waktu server saat ini untuk kalibrasi JS
        ]);
    }

    public function pesan(Request $request)
    {
        $request->validate([
            'meja_id' => 'required|exists:mejas,id',
            'nama_penyewa' => 'required|string|max:255',
            'durasi_jam' => 'required|numeric|min:0.01', // Mengizinkan desimal
        ]);

        // Ambil harga per jam dari pengaturan saat ini
        $hargaSetting = HargaSetting::latest()->first();
        $hargaPerJam = $hargaSetting ? $hargaSetting->harga_per_jam : 0;

        // Simpan penyewaan baru
        Penyewaan::create([
            'meja_id'         => $request->meja_id,
            'nama_penyewa'    => $request->nama_penyewa,
            'durasi_jam'      => $request->durasi_jam,
            'harga_per_jam'   => $hargaPerJam,
            'kode_kupon'      => null,
            'diskon_persen'   => null,
            'total_service'   => 0,
            'service_detail'  => '[]', // Simpan sebagai string JSON kosong
            'total_bayar'     => null,
            'waktu_mulai'     => now(),
            // Waktu selesai dihitung dari waktu mulai + durasi
            'waktu_selesai'   => now()->addMinutes($request->durasi_jam * 60), // Convert jam desimal ke menit
            'status'          => 'berlangsung',
            'kasir_id'        => Auth::id(),
        ]);

        // Update status meja
        Meja::where('id', $request->meja_id)->update([
            'status' => 'dipakai',
        ]);

        return redirect()->route('dashboard.kasir')->with('success', 'Penyewaan dimulai.');
    }

    public function getPenyewaanAktifJson()
    {
        $penyewaan = Penyewaan::with('meja')
            ->where('status', 'berlangsung')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'meja_id' => $item->meja_id,
                    'nama_penyewa' => $item->nama_penyewa,
                    'waktu_mulai' => $item->waktu_mulai->toIso8601String(), // Gunakan ISO8601 untuk parsing JS yang lebih baik
                    'waktu_selesai' => $item->waktu_selesai->toIso8601String(), // Gunakan ISO8601
                    'durasi_jam' => $item->durasi_jam,
                    'meja_nama' => $item->meja->nama_meja,
                    'status' => $item->status, // Kirim status juga (akan selalu 'berlangsung' untuk ini)
                    'harga_per_jam' => $item->harga_per_jam, // Kirim harga per jam untuk perhitungan di frontend
                    'total_service' => $item->total_service, // Kirim total service
                    'service_detail' => json_decode($item->service_detail), // Kirim service detail
                ];
            });

        return response()->json($penyewaan);
    }

    /**
     * Endpoint untuk menandai waktu_selesai penyewaan.
     * Tidak mengubah status, hanya mengupdate waktu_selesai jika penyewaan 'berlangsung'.
     * Digunakan ketika waktu habis atau kasir mengklik "Bayar" lebih awal.
     */
    public function finishPenyewaan(Request $request, Penyewaan $penyewaan)
    {
        // Hanya update jika penyewaan masih 'berlangsung'
        if ($penyewaan->status === 'berlangsung') {
            // Jika waktu_selesai yang sudah ada (berdasarkan durasi awal) masih di masa depan
            // atau belum terisi, set waktu_selesai ke sekarang.
            // Ini menangani kasus user klik bayar lebih awal atau waktu habis.
            if ($penyewaan->waktu_selesai && $penyewaan->waktu_selesai > now()) {
                $penyewaan->waktu_selesai = now();
            } elseif (is_null($penyewaan->waktu_selesai)) {
                $penyewaan->waktu_selesai = now();
            }
            $penyewaan->save();

            return response()->json(['message' => 'Waktu selesai penyewaan berhasil diperbarui.']);
        }

        return response()->json(['message' => 'Penyewaan sudah selesai atau tidak valid untuk pembaruan waktu.'], 400);
    }


    /**
     * Endpoint untuk memproses pembayaran dan menyelesaikan transaksi.
     */
    public function processPayment(Request $request, Penyewaan $penyewaan)
    {
        $request->validate([
            'kode_kupon' => 'nullable|string|max:255',
        ]);

        // Pastikan penyewaan masih 'berlangsung' sebelum diproses pembayarannya
        if ($penyewaan->status !== 'berlangsung') {
            return response()->json(['message' => 'Penyewaan tidak dalam status siap dibayar.'], 400);
        }

        // --- Pastikan waktu_selesai akurat sebelum perhitungan ---
        // Ini penting jika user klik Bayar sebelum waktu habis, atau jika ada masalah di frontend
        // sehingga finishPenyewaan() tidak terpanggil/terupdate.
        if ($penyewaan->waktu_selesai > now()) { // Jika waktu selesai yang tercatat masih di masa depan
            $penyewaan->waktu_selesai = now(); // Update ke waktu saat ini
        }
        $penyewaan->save(); // Simpan perubahan waktu_selesai

        // --- Perhitungan Biaya ---
        // Hitung durasi aktual yang dimainkan (jika player selesai lebih cepat dari durasi booking)
        // Jika player bermain melebihi durasi booking, tetap pakai durasi booking (aturan Anda bisa berbeda)
        // Untuk saat ini, kita akan pakai durasi_jam booking untuk subtotal main.
        // Jika Anda ingin durasi aktual, perlu logika:
        // $actualDurationHours = $penyewaan->waktu_mulai->diffInMinutes($penyewaan->waktu_selesai) / 60;
        // $subtotalMain = $actualDurationHours * $penyewaan->harga_per_jam;
        $subtotalMain = $penyewaan->durasi_jam * $penyewaan->harga_per_jam;

        $diskonPersen = 0;
        $kodeKuponDigunakan = null;

        if ($request->filled('kode_kupon')) {
            $kupon = Kupon::where('kode', $request->kode_kupon)
                          ->where('aktif', true)
                          ->where(function($query) {
                              $query->whereNull('kadaluarsa')
                                    ->orWhere('kadaluarsa', '>=', now());
                          })
                          ->first();

            if ($kupon) {
                $diskonPersen = $kupon->diskon_persen;
                $kodeKuponDigunakan = $kupon->kode;
            } else {
                // Opsional: berikan feedback kupon tidak valid
                // return response()->json(['message' => 'Kupon tidak valid atau sudah kadaluarsa.'], 400);
            }
        }

        $diskon = ($subtotalMain * $diskonPersen) / 100;
        $totalBayar = ($subtotalMain - $diskon) + $penyewaan->total_service;

        // Update penyewaan (status menjadi 'dibayar' HANYA JIKA TOTAL_BAYAR ADA)
        $penyewaan->update([
            'kode_kupon'    => $kodeKuponDigunakan,
            'diskon_persen' => $diskonPersen,
            'total_bayar'   => $totalBayar,
            'status'        => 'dibayar', // Status final: dibayar
        ]);

        // Update status meja menjadi kosong
        Meja::where('id', $penyewaan->meja_id)->update(['status' => 'kosong']);

        return response()->json(['message' => 'Pembayaran sukses!', 'total_bayar' => $totalBayar]);
    }

}