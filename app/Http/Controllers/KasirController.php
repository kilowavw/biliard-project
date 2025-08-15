<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use App\Models\Penyewaan;
use App\Models\HargaSetting;
use App\Models\Kupon;
use App\Models\Service;
use App\Models\Paket; // Import Paket
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    public function dashboard()
    {
        $mejas = Meja::all();
        $penyewaanAktif = Penyewaan::where('status', 'berlangsung')->get();

        return view('kasir.dashboard', compact('mejas', 'penyewaanAktif'))
                   ->with('serverTime', now()->toIso8601String());
    }

    public function pesanDurasi(Request $request)
    {
        $request->validate([
            'meja_id' => 'required|exists:mejas,id',
            'nama_penyewa' => 'required|string|max:255',
            'durasi_jam' => 'required|numeric|min:0.01',
        ]);

        $hargaSetting = HargaSetting::latest()->first();
        $hargaPerJam = $hargaSetting ? $hargaSetting->harga_per_jam : 0;

        Penyewaan::create([
            'meja_id'         => $request->meja_id,
            'nama_penyewa'    => $request->nama_penyewa,
            'durasi_jam'      => $request->durasi_jam,
            'harga_per_jam'   => $hargaPerJam,
            'kode_kupon'      => null,
            'diskon_persen'   => null,
            'total_service'   => 0,
            'service_detail'  => '[]',
            'total_bayar'     => null,
            'waktu_mulai'     => now(),
            'waktu_selesai'   => now()->addMinutes($request->durasi_jam * 60),
            'status'          => 'berlangsung',
            'kasir_id'        => Auth::id(),
            'paket_id'        => null, // Bukan paket
        ]);

        Meja::where('id', $request->meja_id)->update(['status' => 'dipakai']);

        return redirect()->route('dashboard.kasir')->with('success', 'Penyewaan durasi tetap dimulai.');
    }

    public function pesanSepuasnya(Request $request)
    {
        $request->validate([
            'meja_id' => 'required|exists:mejas,id',
            'nama_penyewa' => 'required|string|max:255',
        ]);

        $hargaSetting = HargaSetting::latest()->first();
        $hargaPerJam = $hargaSetting ? $hargaSetting->harga_per_jam : 0;

        Penyewaan::create([
            'meja_id'         => $request->meja_id,
            'nama_penyewa'    => $request->nama_penyewa,
            'durasi_jam'      => 0,
            'harga_per_jam'   => $hargaPerJam,
            'kode_kupon'      => null,
            'diskon_persen'   => null,
            'total_service'   => 0,
            'service_detail'  => '[]',
            'total_bayar'     => null,
            'waktu_mulai'     => now(),
            'waktu_selesai'   => null,
            'status'          => 'berlangsung',
            'kasir_id'        => Auth::id(),
            'paket_id'        => null, // Bukan paket
        ]);

        Meja::where('id', $request->meja_id)->update(['status' => 'dipakai']);

        return redirect()->route('dashboard.kasir')->with('success', 'Penyewaan "Main Sepuasnya" dimulai.');
    }

    /**
     * Handle package booking.
     */
    public function pesanPaket(Request $request)
    {
        $request->validate([
            'meja_id' => 'required|exists:mejas,id',
            'nama_penyewa' => 'required|string|max:255',
            'paket_id' => 'required|exists:pakets,id',
        ]);

        $paket = Paket::find($request->paket_id);
        if (!$paket || !$paket->aktif) {
            throw ValidationException::withMessages([
                'paket_id' => 'Paket tidak valid atau tidak aktif.'
            ]);
        }

        // Hitung total service dari paket
        $totalServicePaket = 0;
        $serviceDetailPaket = [];
        if ($paket->services) {
            foreach ($paket->services as $s_item) {
                // Asumsi structure of $s_item is {'id', 'nama', 'jumlah', 'subtotal'} as stored in Paket->services
                $totalServicePaket += $s_item['subtotal'] ?? ($s_item['jumlah'] * $s_item['harga_per_item']);
                $serviceDetailPaket[] = $s_item;
            }
        }

        Penyewaan::create([
            'meja_id'         => $request->meja_id,
            'nama_penyewa'    => $request->nama_penyewa,
            'durasi_jam'      => $paket->durasi_jam,
            'harga_per_jam'   => 0, // Harga per jam 0 karena sudah termasuk dalam harga paket
            'kode_kupon'      => null,
            'diskon_persen'   => null,
            'total_service'   => $totalServicePaket,
            'service_detail'  => json_encode($serviceDetailPaket),
            'total_bayar'     => null,
            'waktu_mulai'     => now(),
            'waktu_selesai'   => now()->addMinutes($paket->durasi_jam * 60),
            'status'          => 'berlangsung',
            'kasir_id'        => Auth::id(),
            'paket_id'        => $paket->id, // Simpan ID paket
        ]);

        Meja::where('id', $request->meja_id)->update(['status' => 'dipakai']);

        return redirect()->route('dashboard.kasir')->with('success', "Penyewaan dengan paket '{$paket->nama}' dimulai.");
    }

    public function getPenyewaanAktifJson()
    {
        $penyewaan = Penyewaan::with('meja', 'paket') // Eager load paket
            ->where('status', 'berlangsung')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'meja_id' => (int)$item->meja_id,
                    'nama_penyewa' => $item->nama_penyewa,
                    'waktu_mulai' => $item->waktu_mulai->toIso8601String(),
                    'waktu_selesai' => $item->waktu_selesai ? $item->waktu_selesai->toIso8601String() : null,
                    'durasi_jam' => (float)$item->durasi_jam,
                    'harga_per_jam' => (float)$item->harga_per_jam,
                    'meja_nama' => $item->meja->nama_meja,
                    'status' => $item->status,
                    'total_service' => (float)$item->total_service,
                    'service_detail' => json_decode($item->service_detail, true),
                    'is_sepuasnya' => is_null($item->waktu_selesai),
                    'paket_info' => $item->paket ? [ // Informasi paket
                        'id' => $item->paket->id,
                        'nama' => $item->paket->nama,
                        'durasi_jam' => (float)$item->paket->durasi_jam,
                        'harga' => (float)$item->paket->harga,
                        'services' => $item->paket->services, // Services dari paket
                    ] : null,
                ];
            });

        return response()->json($penyewaan);
    }

    // ... (addDuration, addService, removeService methods - no change to these specific methods)
    // processPayment method needs adjustment for paket pricing
    public function processPayment(Request $request, Penyewaan $penyewaan)
    {
        $request->validate([
            'kode_kupon' => 'nullable|string|max:255',
        ]);

        if ($penyewaan->status !== 'berlangsung') {
            return response()->json(['message' => 'Penyewaan tidak dalam status siap dibayar.'], 400);
        }

        $actualDurationMinutes = $penyewaan->waktu_mulai->diffInMinutes(now());
        $actualDurationHours = $actualDurationMinutes / 60;

        if (is_null($penyewaan->waktu_selesai)) {
            $penyewaan->durasi_jam = $actualDurationHours;
            $penyewaan->waktu_selesai = now();
            $penyewaan->save();
        } elseif ($penyewaan->waktu_selesai > now()) {
            $penyewaan->waktu_selesai = now();
            $penyewaan->save();
        }

        // Perhitungan subtotal main: Jika ada paket, gunakan harga paket. Jika tidak, hitung normal.
        $subtotalMain = 0;
        if ($penyewaan->paket) { // Jika penyewaan ini menggunakan paket
            $subtotalMain = $penyewaan->paket->harga; // Gunakan harga paket
            // Jika ada durasi tambahan (setelah paket habis dan durasi diperpanjang),
            // Anda perlu logika tambahan di sini untuk menghitung biaya perpanjangan.
            // Untuk saat ini, kita anggap harga paket sudah final untuk waktu paket.
        } else {
            $subtotalMain = (is_null($penyewaan->getOriginal('waktu_selesai')) ? $actualDurationHours : $penyewaan->durasi_jam) * $penyewaan->harga_per_jam;
        }


        $totalBeforeDiscount = $subtotalMain + $penyewaan->total_service;

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
            }
        }

        // kalo mau diskon subtotal saja tidak termasuk service ubah juga di js pada dashboard kasir fetchpembayaran
        // $diskon = ($subtotalMain * $diskonPersen) / 100;
        // $totalBayar = ($subtotalMain - $diskon) + $penyewaan->total_service;
        $totalSebelumDiskon = ($penyewaan->durasi_jam * $penyewaan->harga_per_jam) + $penyewaan->total_service;
        $diskon = ($totalSebelumDiskon * $diskonPersen) / 100;
        $totalBayar = $totalSebelumDiskon - $diskon;

        $penyewaan->update([
            'kode_kupon'    => $kodeKuponDigunakan,
            'diskon_persen' => $diskonPersen,
            'total_bayar'   => $totalBayar,
            'status'        => 'dibayar',
        ]);

        Meja::where('id', $penyewaan->meja_id)->update(['status' => 'kosong']);

        return response()->json(['message' => 'Pembayaran sukses!', 'total_bayar' => $totalBayar]);
    }
}