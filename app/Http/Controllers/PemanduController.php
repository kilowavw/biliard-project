<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use App\Models\Penyewaan;
use App\Models\HargaSetting;
use App\Models\Kupon; // Dipertahankan karena Pemandu bisa lihat kode kupon (tapi tidak memproses)
use App\Models\Service;
use App\Models\Paket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PemanduController extends Controller
{
    // Salin seluruh isi dari KasirController kecuali 'processPayment'

    public function dashboard()
    {
        $mejas = Meja::all();
        $penyewaanAktif = Penyewaan::where('status', 'berlangsung')->get(); // Diperlukan untuk tampilan awal
        $activePakets = Paket::where('aktif', true)->orderBy('nama_paket')->get();

        return view('pemandu.dashboard', compact('mejas', 'penyewaanAktif', 'activePakets'))
                    ->with('serverTime', now()->toIso8601String());
    }

    public function pesanDurasi(Request $request)
    {
       try{
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
            'kasir_id'        => null, // Pemandu tidak memproses pembayaran
            'pemandu_id'      => Auth::id(), // ID Pemandu
            'paket_id'        => null,
        ]);

        Meja::where('id', $request->meja_id)->update(['status' => 'dipakai']);

      return response()->json(['message' => 'Penyewaan durasi tetap dimulai oleh Pemandu!', 'success' => true], 200);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error pesan durasi by Pemandu: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memulai penyewaan durasi. Silakan coba lagi.'], 500);
        }    
    }

    public function pesanSepuasnya(Request $request)
    {
      try{
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
            'kasir_id'        => null, // Pemandu tidak memproses pembayaran
            'pemandu_id'      => Auth::id(), // ID Pemandu
            'paket_id'        => null,
        ]);

        Meja::where('id', $request->meja_id)->update(['status' => 'dipakai']);
        return response()->json(['message' => 'Penyewaan durasi tetap dimulai oleh Pemandu!', 'success' => true], 200);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error pesan durasi by Pemandu: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memulai penyewaan durasi. Silakan coba lagi.'], 500);
        }    
    }

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
                'paket_id' => 'Paket tidak ditemukan atau tidak aktif.'
            ]);
        }

        $paketDetails = $paket->isi_paket;

        if (!isset($paketDetails['harga_paket']) || !isset($paketDetails['durasi_jam']) || !isset($paketDetails['services'])) {
            throw ValidationException::withMessages([
                'paket_id' => 'Struktur detail paket tidak valid.'
            ]);
        }

        $durasiJam = (float)$paketDetails['durasi_jam'];
        $hargaPokokSewa = (float)$paketDetails['harga_paket'];
        $waktuSelesai = $durasiJam === 0.0 ? null : now()->addMinutes($durasiJam * 60);

        $totalServicePaket = 0;
        $serviceDetailPaket = [];
        $servicesToDecrement = [];

        if (is_array($paketDetails['services'])) {
            foreach ($paketDetails['services'] as $srv) {
                if (isset($srv['id']) && isset($srv['nama']) && isset($srv['jumlah']) && isset($srv['subtotal'])) {
                    $serviceDetailPaket[] = [
                        'id' => (int)$srv['id'],
                        'nama' => $srv['nama'],
                        'jumlah' => (int)$srv['jumlah'],
                        'subtotal' => (float)$srv['subtotal'],
                    ];
                    $totalServicePaket += (float)$srv['subtotal'];
                    $servicesToDecrement[] = ['id' => (int)$srv['id'], 'jumlah' => (int)$srv['jumlah'], 'nama' => $srv['nama']];
                }
            }
        }

        DB::beginTransaction();
        try {
            foreach ($servicesToDecrement as $srvItem) {
                $service = Service::lockForUpdate()->find($srvItem['id']);
                if (!$service) {
                    DB::rollBack();
                    throw ValidationException::withMessages([
                        'paket_id' => "Service '{$srvItem['nama']}' dalam paket tidak ditemukan."
                    ]);
                }
                if ($service->stok < $srvItem['jumlah']) {
                    DB::rollBack();
                    throw ValidationException::withMessages([
                        'paket_id' => "Stok {$service->nama} kurang untuk paket. Tersedia: {$service->stok}"
                    ]);
                }
                $service->decrement('stok', $srvItem['jumlah']);
            }

            Penyewaan::create([
                'meja_id'         => $request->meja_id,
                'nama_penyewa'    => $request->nama_penyewa,
                'durasi_jam'      => $durasiJam,
                'harga_per_jam'   => $hargaPokokSewa,
                'kode_kupon'      => null,
                'diskon_persen'   => null,
                'total_service'   => $totalServicePaket,
                'service_detail'  => json_encode($serviceDetailPaket),
                'total_bayar'     => null,
                'waktu_mulai'     => now(),
                'waktu_selesai'   => $waktuSelesai,
                'status'          => 'berlangsung',
                'kasir_id'        => null, // Diatur null
                'pemandu_id'      => Auth::id(), // ID Pemandu
                'paket_id'        => $request->paket_id,
            ]);

            Meja::where('id', $request->meja_id)->update(['status' => 'dipakai']);

            DB::commit();

            return redirect()->route('dashboard.pemandu')->with('success', "Penyewaan dengan paket '{$paket->nama_paket}' dimulai oleh Pemandu.");

        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error booking package by Pemandu: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memulai penyewaan paket oleh Pemandu. Silakan coba lagi.');
        }
    }

    public function getPenyewaanAktifJson()
    {
        $penyewaan = Penyewaan::with(['meja', 'pemandu'])
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
                    'meja_nama' => $item->meja->nama_meja,
                    'status' => $item->status,
                    'harga_per_jam' => (float)$item->harga_per_jam,
                    'total_service' => (float)$item->total_service,
                    'service_detail' => json_decode($item->service_detail, true),
                    'is_sepuasnya' => is_null($item->waktu_selesai),
                    'paket_id' => $item->paket_id,
                    'pemandu_nama' => $item->pemandu ? $item->pemandu->name : 'N/A',
                ];
            });

        return response()->json($penyewaan);
    }

    public function addDuration(Request $request, Penyewaan $penyewaan)
    {
        $request->validate(['additional_durasi_jam' => 'required|numeric|min:0.01']);

        if ($penyewaan->status !== 'berlangsung' || is_null($penyewaan->waktu_selesai)) {
            return response()->json(['message' => 'Tidak bisa menambah durasi untuk penyewaan ini.'], 400);
        }

        $penyewaan->durasi_jam += (float)$request->additional_durasi_jam;
        $penyewaan->waktu_selesai = $penyewaan->waktu_selesai->addMinutes((float)$request->additional_durasi_jam * 60);
        $penyewaan->save();

        return response()->json(['message' => 'Durasi berhasil ditambahkan!', 'new_durasi_jam' => $penyewaan->durasi_jam, 'new_waktu_selesai' => $penyewaan->waktu_selesai->toIso8601String()]);
    }

    public function addService(Request $request, Penyewaan $penyewaan)
    {
        $request->validate([
            'services' => 'required|array',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.jumlah' => 'required|integer|min:1',
        ]);

        if ($penyewaan->status !== 'berlangsung') {
            return response()->json(['message' => 'Penyewaan tidak aktif.'], 400);
        }

        DB::beginTransaction();
        try {
            $existingServiceDetail = json_decode($penyewaan->service_detail, true) ?: [];
            $totalServiceTambahan = 0;

            foreach ($request->services as $serviceItem) {
                $service = Service::lockForUpdate()->find($serviceItem['service_id']);
                if (!$service) {
                    DB::rollBack();
                    return response()->json(['message' => 'Service tidak ditemukan.'], 404);
                }

                if ($service->stok < $serviceItem['jumlah']) {
                    DB::rollBack();
                    return response()->json(['message' => "Stok {$service->nama} tidak cukup. Tersedia: {$service->stok}"], 400);
                }

                $subtotal = $service->harga * $serviceItem['jumlah'];
                $totalServiceTambahan += $subtotal;

                $found = false;
                foreach ($existingServiceDetail as &$detail) {
                    if (isset($detail['id']) && $detail['id'] == $service->id) {
                        $detail['jumlah'] += $serviceItem['jumlah'];
                        $detail['subtotal'] += $subtotal;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $existingServiceDetail[] = [
                        'id' => $service->id,
                        'nama' => $service->nama,
                        'jumlah' => $serviceItem['jumlah'],
                        'subtotal' => $subtotal,
                    ];
                }

                $service->decrement('stok', $serviceItem['jumlah']);
            }

            $penyewaan->service_detail = json_encode($existingServiceDetail);
            $penyewaan->total_service += $totalServiceTambahan;
            $penyewaan->save();

            DB::commit();
            return response()->json(['message' => 'Service berhasil ditambahkan oleh Pemandu!', 'new_total_service' => $penyewaan->total_service, 'new_service_detail' => $existingServiceDetail]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding service by Pemandu: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menambahkan service. Silakan coba lagi.'], 500);
        }
    }

    public function removeService(Request $request, Penyewaan $penyewaan)
    {
        $request->validate([
            'service_id' => 'required|integer|exists:services,id',
        ]);

        if ($penyewaan->status !== 'berlangsung') {
            return response()->json(['message' => 'Penyewaan tidak aktif.'], 400);
        }

        DB::beginTransaction();
        try {
            $existingServiceDetail = json_decode($penyewaan->service_detail, true) ?: [];
            $serviceToRemoveId = $request->service_id;
            $removedSubtotal = 0;
            $removedJumlah = 0;
            $updatedServiceDetail = [];
            $serviceName = '';

            foreach ($existingServiceDetail as $detail) {
                if (isset($detail['id']) && $detail['id'] == $serviceToRemoveId) {
                    $removedSubtotal += $detail['subtotal'];
                    $removedJumlah += $detail['jumlah'];
                    $serviceName = $detail['nama'];
                } else {
                    $updatedServiceDetail[] = $detail;
                }
            }

            if ($removedSubtotal === 0) {
                DB::rollBack();
                return response()->json(['message' => 'Service tidak ditemukan dalam daftar penyewaan.'], 404);
            }

            $service = Service::lockForUpdate()->find($serviceToRemoveId);
            if ($service) {
                $service->increment('stok', $removedJumlah);
            }

            $penyewaan->service_detail = json_encode($updatedServiceDetail);
            $penyewaan->total_service -= $removedSubtotal;
            $penyewaan->save();

            DB::commit();
            return response()->json(['message' => "Service '{$serviceName}' berhasil dihapus oleh Pemandu!", 'new_total_service' => $penyewaan->total_service, 'new_service_detail' => $updatedServiceDetail]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error removing service by Pemandu: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus service. Silakan coba lagi.'], 500);
        }
    }

    // Metode `processPayment` dari KasirController TIDAK DISALIN ke sini.
}