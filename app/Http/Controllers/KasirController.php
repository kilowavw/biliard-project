<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use App\Models\Penyewaan;
use App\Models\HargaSetting;
use App\Models\Kupon;
use App\Models\Service;
use App\Models\Paket;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Pastikan ini ada

class KasirController extends Controller
{
    public function dashboard()
    {
        $mejas = Meja::all();
        $penyewaanAktif = Penyewaan::where('status', 'berlangsung')->get();
        $userRole = Auth::user() ? Auth::user()->role : 'guest';

        $activePakets = Paket::where('aktif', true)->orderBy('nama_paket')->get();

        return view('kasir.dashboard', compact('mejas', 'penyewaanAktif', 'userRole', 'activePakets'))
                    ->with('serverTime', now()->toIso8601String());
    }

    public function pesanDurasi(Request $request)
    {
        try {
            $request->validate([
                'meja_id' => 'required|exists:mejas,id',
                'nama_penyewa' => 'required|string|max:255',
                'durasi_jam' => 'required|numeric|min:0.01',
                'kode_member' => 'nullable|string|max:255',
            ]);

            $hargaSetting = HargaSetting::latest()->first();
            $hargaPerJam = $hargaSetting ? $hargaSetting->harga_per_jam : 0;

            $diskonMember = 0;
            $memberId = null;

            if ($request->filled('kode_member')) {
                $member = Member::where('kode_member', $request->kode_member)
                                ->where('status_keanggotaan', 'Aktif')
                                ->where('tanggal_kadaluarsa', '>=', now())
                                ->first();
                if ($member) {
                    $diskonMember = $member->diskon_persen;
                    $memberId = $member->id;
                } else {
                    return response()->json(['message' => 'Kode Member tidak valid atau sudah kadaluarsa.', 'success' => false], 400);
                }
            }

            Penyewaan::create([
                'meja_id'         => $request->meja_id,
                'nama_penyewa'    => $request->nama_penyewa,
                'durasi_jam'      => $request->durasi_jam,
                'harga_per_jam'   => $hargaPerJam,
                'kode_kupon'      => null,
                'diskon_persen'   => 0,
                'total_service'   => 0,
                'service_detail'  => '[]',
                'total_bayar'     => null,
                'waktu_mulai'     => now(),
                'waktu_selesai'   => now()->addMinutes($request->durasi_jam * 60),
                'status'          => 'berlangsung',
                'kasir_id'        => Auth::id(),
                'member_id'       => $memberId,
                'diskon_member_persen' => $diskonMember,
                'paket_id'        => null,
            ]);

            Meja::where('id', $request->meja_id)->update(['status' => 'dipakai']);

            return response()->json(['message' => 'Penyewaan durasi tetap dimulai.', 'success' => true], 200);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            Log::error('Error pesan durasi by Kasir: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memulai penyewaan durasi. Silakan coba lagi.', 'success' => false], 500);
        }
    }

    public function pesanSepuasnya(Request $request)
    {
        try {
            $request->validate([
                'meja_id' => 'required|exists:mejas,id',
                'nama_penyewa' => 'required|string|max:255',
                'kode_member' => 'nullable|string|max:255',
            ]);

            $hargaSetting = HargaSetting::latest()->first();
            $hargaPerJam = $hargaSetting ? $hargaSetting->harga_per_jam : 0;

            $diskonMember = 0;
            $memberId = null;

            if ($request->filled('kode_member')) {
                $member = Member::where('kode_member', $request->kode_member)
                                ->where('status_keanggotaan', 'Aktif')
                                ->where('tanggal_kadaluarsa', '>=', now())
                                ->first();
                if ($member) {
                    $diskonMember = $member->diskon_persen;
                    $memberId = $member->id;
                } else {
                    return response()->json(['message' => 'Kode Member tidak valid atau sudah kadaluarsa.', 'success' => false], 400);
                }
            }

            Penyewaan::create([
                'meja_id'         => $request->meja_id,
                'nama_penyewa'    => $request->nama_penyewa,
                'durasi_jam'      => 0,
                'harga_per_jam'   => $hargaPerJam,
                'kode_kupon'      => null,
                'diskon_persen'   => 0,
                'total_service'   => 0,
                'service_detail'  => '[]',
                'total_bayar'     => null,
                'waktu_mulai'     => now(),
                'waktu_selesai'   => null,
                'status'          => 'berlangsung',
                'kasir_id'        => Auth::id(),
                'is_sepuasnya'    => true,
                'member_id'       => $memberId,
                'diskon_member_persen' => $diskonMember,
                'paket_id'        => null,
            ]);

            Meja::where('id', $request->meja_id)->update(['status' => 'dipakai']);

            return response()->json(['message' => 'Penyewaan "Main Sepuasnya" dimulai.', 'success' => true], 200);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            Log::error('Error pesan sepuasnya by Kasir: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memulai penyewaan "Main Sepuasnya". Silakan coba lagi.', 'success' => false], 500);
        }
    }

    public function pesanPaket(Request $request)
    {
        try {
            $request->validate([
                'meja_id' => 'required|exists:mejas,id',
                'nama_penyewa' => 'required|string|max:255',
                'paket_id' => 'required|exists:pakets,id',
                'kode_member' => 'nullable|string|max:255',
            ]);

            $paket = Paket::find($request->paket_id);
            if (!$paket || !$paket->aktif) {
                throw ValidationException::withMessages([
                    'paket_id' => 'Paket tidak ditemukan atau tidak aktif.'
                ]);
            }

            $paketDetails = $paket->isi_paket;

            if (!isset($paketDetails['harga_paket']) || !isset($paketDetails['durasi_jam'])) {
                throw ValidationException::withMessages([
                    'paket_id' => 'Struktur detail paket tidak valid (harga_paket atau durasi_jam tidak ditemukan).'
                ]);
            }

            $durasiJam = (float)$paketDetails['durasi_jam'];
            $hargaPokokSewa = (float)$paketDetails['harga_paket']; // Harga pokok adalah harga paket
            $waktuSelesai = $durasiJam === 0.0 ? null : now()->addMinutes($durasiJam * 60);

            $totalServicePaket = 0; // Total service yang termasuk dalam paket dianggap 0 karena sudah dihitung dalam harga_paket
            $serviceDetailPaket = [];
            $servicesToDecrement = [];

            if (isset($paketDetails['services']) && is_array($paketDetails['services'])) {
                foreach ($paketDetails['services'] as $srv) {
                    if (isset($srv['id']) && isset($srv['nama']) && isset($srv['jumlah'])) {
                        $serviceDetailPaket[] = [
                            'id' => (int)$srv['id'],
                            'nama' => $srv['nama'],
                            'jumlah' => (int)$srv['jumlah'],
                            'subtotal' => 0, // Subtotal service dari paket selalu 0
                        ];
                        $servicesToDecrement[] = ['id' => (int)$srv['id'], 'jumlah' => (int)$srv['jumlah'], 'nama' => $srv['nama']];
                    }
                }
            }

            $diskonMember = 0;
            $memberId = null;

            if ($request->filled('kode_member')) {
                $member = Member::where('kode_member', $request->kode_member)
                                ->where('status_keanggotaan', 'Aktif')
                                ->where('tanggal_kadaluarsa', '>=', now())
                                ->first();
                if ($member) {
                    $diskonMember = $member->diskon_persen;
                    $memberId = $member->id;
                } else {
                    return response()->json(['message' => 'Kode Member tidak valid atau sudah kadaluarsa.', 'success' => false], 400);
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
                    'diskon_persen'   => 0,
                    'total_service'   => $totalServicePaket,
                    'service_detail'  => json_encode($serviceDetailPaket),
                    'total_bayar'     => null,
                    'waktu_mulai'     => now(),
                    'waktu_selesai'   => $waktuSelesai,
                    'status'          => 'berlangsung',
                    'kasir_id'        => Auth::id(),
                    'paket_id'        => $request->paket_id,
                    'is_sepuasnya'    => ($durasiJam === 0.0),
                    'member_id'       => $memberId,
                    'diskon_member_persen' => $diskonMember,
                ]);

                Meja::where('id', $request->meja_id)->update(['status' => 'dipakai']);

                DB::commit();

                return response()->json(['message' => "Penyewaan dengan paket '{$paket->nama_paket}' dimulai.", 'success' => true], 200);

            } catch (ValidationException $e) {
                DB::rollBack();
                return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error booking package: ' . $e->getMessage());
                return response()->json(['message' => 'Gagal memulai penyewaan paket. Silakan coba lagi.', 'success' => false], 500);
            }
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            Log::error('Error pesan paket by Kasir (outer catch): ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memulai penyewaan paket. Silakan coba lagi.', 'success' => false], 500);
        }
    }


    public function getPenyewaanAktifJson()
    {
        $penyewaan = Penyewaan::with('meja', 'member', 'paket')
        ->where('status', 'berlangsung')
        ->get()
        ->map(function ($item) {
                if (!is_null($item->waktu_selesai) && $item->waktu_selesai->isPast() && $item->meja->status === 'dipakai') {
                    $item->meja->update(['status' => 'waktu_habis']);
                    Log::info("Meja {$item->meja->nama_meja} (ID: {$item->meja->id}) otomatis diubah menjadi 'waktu_habis'.");
                }

                $effectiveHarga = $item->harga_per_jam;
                $effectiveDurasi = (float)$item->durasi_jam;

                // --- PERUBAHAN DI SINI: Kembalikan semua service_detail dari database ---
                $originalServiceDetails = json_decode($item->service_detail, true) ?: [];
                // Frontend akan memfilter untuk tampilan "layanan tambahan"
                // total_service dari database sudah hanya service tambahan, jadi biarkan saja
                // $item->total_service sudah berisi total service tambahan yang dihitung di addService
                // Jika service paket dibuat dengan subtotal 0, maka ini tidak akan menambah total_service
                // di database, jadi total_service yang disimpan sudah benar.
                // ServiceDetailUntukDisplay akan menampilkan semua layanan yang dicatat di penyewaan.
                // Biarkan frontend yang membedakan mana yang gratis/berbayar
                // --- AKHIR PERUBAHAN ---

                return [
                    'id' => $item->id,
                    'meja_id' => (int)$item->meja_id,
                    'nama_penyewa' => $item->nama_penyewa,
                    'waktu_mulai' => $item->waktu_mulai->toIso8601String(),
                    'waktu_selesai' => $item->waktu_selesai ? $item->waktu_selesai->toIso8601String() : null,
                    'durasi_jam' => $effectiveDurasi,
                    'meja_nama' => $item->meja->nama_meja,
                    'status_meja_saat_ini' => $item->meja->status,
                    'status' => $item->status,
                    'harga_per_jam' => $effectiveHarga,
                    'total_service' => (float)$item->total_service, // Ini adalah total service tambahan saja
                    'service_detail' => $originalServiceDetails, // Sekarang mengembalikan semua detail service
                    'is_sepuasnya' => is_null($item->waktu_selesai),
                    'paket_id' => $item->paket_id,
                    'member_id' => $item->member_id,
                    'diskon_member_persen' => (float)$item->diskon_member_persen,
                    'member_nama' => $item->member ? $item->member->nama_member : null,
                    'member_kode' => $item->member ? $item->member->kode_member : null,
                    'nama_paket' => $item->paket ? $item->paket->nama_paket : null,
                ];
            });

        return response()->json($penyewaan);
    }

    public function addDuration(Request $request, Penyewaan $penyewaan)
    {
        try {
            $request->validate(['additional_durasi_jam' => 'required|numeric|min:0.01']);

            if ($penyewaan->status !== 'berlangsung' || is_null($penyewaan->waktu_selesai)) {
                return response()->json(['message' => 'Tidak bisa menambah durasi untuk penyewaan ini.', 'success' => false], 400);
            }
            
            if ($penyewaan->paket_id && $penyewaan->paket) {
                $paketDetails = $penyewaan->paket->isi_paket;
                if (isset($paketDetails['durasi_jam']) && (float)$paketDetails['durasi_jam'] > 0) {
                     return response()->json(['message' => 'Tidak bisa menambah durasi untuk penyewaan paket dengan durasi tetap.', 'success' => false], 400);
                }
            }
            

            $penyewaan->durasi_jam += (float)$request->additional_durasi_jam;
            $penyewaan->waktu_selesai = $penyewaan->waktu_selesai->addMinutes((float)$request->additional_durasi_jam * 60);
            $penyewaan->save();

            return response()->json(['message' => 'Durasi berhasil ditambahkan!', 'new_durasi_jam' => $penyewaan->durasi_jam, 'new_waktu_selesai' => $penyewaan->waktu_selesai->toIso8601String(), 'success' => true]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            Log::error('Error adding duration: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menambahkan durasi. Silakan coba lagi.', 'success' => false], 500);
        }
    }

    public function addService(Request $request, Penyewaan $penyewaan)
    {
        try {
            $request->validate([
                'services' => 'required|array',
                'services.*.service_id' => 'required|exists:services,id',
                'services.*.jumlah' => 'required|integer|min:1',
            ]);

            if ($penyewaan->status !== 'berlangsung') {
                return response()->json(['message' => 'Penyewaan tidak aktif.', 'success' => false], 400);
            }

            DB::beginTransaction();
            try {
                $existingServiceDetail = json_decode($penyewaan->service_detail, true) ?: [];
                $totalServiceTambahanSaatIni = 0;

                foreach ($request->services as $serviceItem) {
                    $service = Service::lockForUpdate()->find($serviceItem['service_id']);
                    if (!$service) {
                        DB::rollBack();
                        return response()->json(['message' => 'Service tidak ditemukan.', 'success' => false], 404);
                    }

                    if ($service->stok < $serviceItem['jumlah']) {
                        DB::rollBack();
                        return response()->json(['message' => "Stok {$service->nama} tidak cukup. Tersedia: {$service->stok}", 'success' => false], 400);
                    }

                    $subtotal = $service->harga * $serviceItem['jumlah'];
                    $totalServiceTambahanSaatIni += $subtotal;

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
                $penyewaan->total_service += $totalServiceTambahanSaatIni;
                $penyewaan->save();

                DB::commit();
                return response()->json(['message' => 'Service berhasil ditambahkan!', 'new_total_service' => $penyewaan->total_service, 'new_service_detail' => $existingServiceDetail, 'success' => true]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error adding service (inner catch): ' . $e->getMessage());
                return response()->json(['message' => 'Gagal menambahkan service. Silakan coba lagi.', 'success' => false], 500);
            }
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            Log::error('Error adding service (outer catch): ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menambahkan service. Silakan coba lagi.', 'success' => false], 500);
        }
    }

    public function removeService(Request $request, Penyewaan $penyewaan)
    {
        try {
            $request->validate([
                'service_id' => 'required|integer|exists:services,id',
            ]);

            if ($penyewaan->status !== 'berlangsung') {
                return response()->json(['message' => 'Penyewaan tidak aktif.', 'success' => false], 400);
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
                        if (isset($detail['subtotal']) && (float)$detail['subtotal'] > 0) {
                            $removedSubtotal += (float)$detail['subtotal'];
                            $removedJumlah += (int)$detail['jumlah'];
                        }
                        $serviceName = $detail['nama'];
                    } else {
                        $updatedServiceDetail[] = $detail;
                    }
                }


                // Penghapusan service di paket
                // if ($removedSubtotal === 0 && $removedJumlah === 0) {
                //     DB::rollBack();
                //     return response()->json(['message' => 'Service tidak ditemukan atau merupakan service paket yang tidak dikenakan biaya tambahan.', 'success' => false], 404);
                // }

                if ($removedJumlah > 0) {
                    $service = Service::lockForUpdate()->find($serviceToRemoveId);
                    if ($service) {
                        $service->increment('stok', $removedJumlah);
                    }
                }
                
                $penyewaan->service_detail = json_encode($updatedServiceDetail);
                $penyewaan->total_service -= $removedSubtotal;
                $penyewaan->save();

                DB::commit();
                return response()->json(['message' => "Service '{$serviceName}' berhasil dihapus!", 'new_total_service' => $penyewaan->total_service, 'new_service_detail' => $updatedServiceDetail, 'success' => true]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error removing service (inner catch): ' . $e->getMessage());
                return response()->json(['message' => 'Gagal menghapus service. Silakan coba lagi.', 'success' => false], 500);
            }
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            Log::error('Error removing service (outer catch): ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus service. Silakan coba lagi.', 'success' => false], 500);
        }
    }


    public function calculatePaymentDetails(Request $request, Penyewaan $penyewaan)
    {
        try {
            $request->validate([
                'kode_kupon' => 'nullable|string|max:255',
            ]);

            // --- Mulai logika perhitungan (mereplikasi processPayment tanpa menyimpan ke DB) ---

            // Clone model untuk menghindari perubahan pada instance asli jika ada save() yang tidak sengaja
            $tempPenyewaan = clone $penyewaan;

            // Simpan waktu selesai asli untuk menentukan tipe booking 'sepuasnya'
            // Penting: $penyewaan->getOriginal('waktu_selesai') akan mengambil nilai dari DB saat model dimuat.
            // Jika model sudah diubah sebelum endpoint ini dipanggil, nilai ini mungkin sudah berbeda.
            // Lebih baik gunakan flag is_sepuasnya dari DB dan durasi paket 0.
            $originalWaktuSelesaiDb = $penyewaan->getOriginal('waktu_selesai');


            $actualDurationMinutes = $tempPenyewaan->waktu_mulai->diffInMinutes(now());
            $actualDurationHours = $actualDurationMinutes / 60;

            if (is_null($tempPenyewaan->waktu_selesai)) {
                $tempPenyewaan->durasi_jam = $actualDurationHours;
                $tempPenyewaan->waktu_selesai = now();
            } elseif ($tempPenyewaan->waktu_selesai > now()) {
                $tempPenyewaan->waktu_selesai = now();
                $tempPenyewaan->durasi_jam = $tempPenyewaan->waktu_mulai->diffInMinutes($tempPenyewaan->waktu_selesai) / 60;
            }
            // TIDAK PERLU $tempPenyewaan->save() di sini karena ini hanya untuk perhitungan tampilan.

            $tempPenyewaan->loadMissing('paket'); // Pastikan paket dimuat

            $totalBayarSebelumDiskon = 0;
            $subtotalSewaUntukDisplay = 0;

            if ($tempPenyewaan->paket_id && $tempPenyewaan->paket) {
                $paketDetails = $tempPenyewaan->paket->isi_paket;
                if (isset($paketDetails['harga_paket'])) {
                    $subtotalSewaUntukDisplay = (float)$paketDetails['harga_paket'];
                    $totalBayarSebelumDiskon = (float)$paketDetails['harga_paket'];
                    $totalBayarSebelumDiskon += (float)$tempPenyewaan->total_service;
                } else {
                    Log::warning("Harga paket tidak ditemukan dalam detail paket ID: {$tempPenyewaan->paket_id} untuk penyewaan ID: {$tempPenyewaan->id} saat menghitung tampilan.");
                    $subtotalSewaUntukDisplay = $tempPenyewaan->durasi_jam * $tempPenyewaan->harga_per_jam;
                    $totalBayarSebelumDiskon = $subtotalSewaUntukDisplay + (float)$tempPenyewaan->total_service;
                }
            } else {
                $subtotalSewaUntukDisplay = $tempPenyewaan->durasi_jam * $tempPenyewaan->harga_per_jam;
                $totalBayarSebelumDiskon = $subtotalSewaUntukDisplay + (float)$tempPenyewaan->total_service;
            }

            $diskonMemberPersen = (float)$tempPenyewaan->diskon_member_persen;
            $diskonPersenKupon = 0;
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
                    $diskonPersenKupon = $kupon->diskon_persen;
                    $kodeKuponDigunakan = $kupon->kode;
                }
            }

            $totalDiskonGabunganPersen = $diskonMemberPersen + $diskonPersenKupon;
            $finalDiskonPersen = min($totalDiskonGabunganPersen, 100);

            $totalDiskonAmount = ($totalBayarSebelumDiskon * $finalDiskonPersen) / 100;
            $finalTotalBayar = $totalBayarSebelumDiskon - $totalDiskonAmount;

            if ($finalTotalBayar < 0) {
                $finalTotalBayar = 0;
            }

            // Logika penentuan apakah pembulatan ke ribuan terdekat harus diterapkan
            // Menggunakan properti is_sepuasnya dari model asli ($penyewaan) yang dimuat dari DB.
            $isSepuasnyaTypeBooking = $penyewaan->is_sepuasnya ||
                                     ($penyewaan->paket_id && $penyewaan->paket && (float)($penyewaan->paket->isi_paket['durasi_jam'] ?? 1) === 0.0) ||
                                     ($originalWaktuSelesaiDb === null);

            if ($isSepuasnyaTypeBooking) {
                $finalTotalBayar = round((float) $finalTotalBayar, -3); // Bulatkan ke ribuan terdekat
                $finalTotalBayar = (int) $finalTotalBayar; // Cast ke integer untuk DECIMAL(10,0) kolom
            } else {
                // Untuk penyewaan durasi tetap standar, bulatkan ke bilangan bulat terdekat (tanpa desimal)
                $finalTotalBayar = round((float) $finalTotalBayar, 0);
                $finalTotalBayar = (int) $finalTotalBayar; // Cast ke integer untuk DECIMAL(10,0) kolom
            }

            // --- Akhir logika perhitungan ---

            return response()->json([
                'success' => true,
                'subtotal_sewa' => (float)$subtotalSewaUntukDisplay,
                'total_service_tambahan' => (float)$tempPenyewaan->total_service,
                'diskon_member_persen' => $diskonMemberPersen,
                'diskon_kupon_persen' => $diskonPersenKupon,
                'total_diskon_amount' => $totalDiskonAmount,
                'final_total_bayar' => $finalTotalBayar,
                'kode_kupon_digunakan' => $kodeKuponDigunakan,
                'calculated_durasi_jam' => $tempPenyewaan->durasi_jam, // Durasi aktual yang dihitung untuk tampilan
                'calculated_waktu_selesai' => $tempPenyewaan->waktu_selesai ? $tempPenyewaan->waktu_selesai->toIso8601String() : null, // Waktu selesai aktual yang dihitung
            ]);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            Log::error('Error calculating payment details for display: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json(['message' => 'Gagal menghitung detail pembayaran. Silakan coba lagi.', 'success' => false], 500);
        }
    }

    public function processPayment(Request $request, Penyewaan $penyewaan)
    {
        try {
            $request->validate([
                'kode_kupon' => 'nullable|string|max:255',
                'is_qris' => 'boolean',
            ]);

            if ($penyewaan->status !== 'berlangsung' && $penyewaan->status !== 'waktu_habis') {
                 return response()->json(['message' => 'Penyewaan tidak dalam status siap dibayar.', 'success' => false], 400);
            }
            if ($penyewaan->meja->status === 'kosong') {
                return response()->json(['message' => 'Meja sudah kosong dan tidak dapat diproses pembayarannya.', 'success' => false], 400);
            }

            // Simpan nilai waktu_selesai sebelum diubah
            $originalWaktuSelesai = $penyewaan->getOriginal('waktu_selesai');

            $actualDurationMinutes = $penyewaan->waktu_mulai->diffInMinutes(now());
            $actualDurationHours = $actualDurationMinutes / 60;

            if (is_null($penyewaan->waktu_selesai)) {
                $penyewaan->durasi_jam = $actualDurationHours;
                $penyewaan->waktu_selesai = now();
            } elseif ($penyewaan->waktu_selesai > now()) {
                $penyewaan->waktu_selesai = now();
            }
            $penyewaan->save(); // Simpan perubahan durasi_jam dan waktu_selesai

            $penyewaan->loadMissing('paket');

            $totalBayarSebelumDiskon = 0;
            $subtotalSewaUntukDisplay = 0;

            if ($penyewaan->paket_id && $penyewaan->paket) {
                $paketDetails = $penyewaan->paket->isi_paket;
                if (isset($paketDetails['harga_paket'])) {
                    $subtotalSewaUntukDisplay = (float)$paketDetails['harga_paket'];
                    $totalBayarSebelumDiskon = (float)$paketDetails['harga_paket'];
                    $totalBayarSebelumDiskon += (float)$penyewaan->total_service;
                } else {
                    Log::warning("Harga paket tidak ditemukan dalam detail paket ID: {$penyewaan->paket_id} untuk penyewaan ID: {$penyewaan->id}");
                    $subtotalSewaUntukDisplay = $penyewaan->durasi_jam * $penyewaan->harga_per_jam;
                    $totalBayarSebelumDiskon = $subtotalSewaUntukDisplay + (float)$penyewaan->total_service;
                }
            } else {
                $subtotalSewaUntukDisplay = $penyewaan->durasi_jam * $penyewaan->harga_per_jam;
                $totalBayarSebelumDiskon = $subtotalSewaUntukDisplay + (float)$penyewaan->total_service;
            }

            $diskonMemberPersen = (float)$penyewaan->diskon_member_persen;
            $diskonPersenKupon = 0;
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
                    $diskonPersenKupon = $kupon->diskon_persen;
                    $kodeKuponDigunakan = $kupon->kode;
                }
            }

            $totalDiskonGabunganPersen = $diskonMemberPersen + $diskonPersenKupon;
            $finalDiskonPersen = min($totalDiskonGabunganPersen, 100);

            $totalDiskonAmount = ($totalBayarSebelumDiskon * $finalDiskonPersen) / 100;
            $finalTotalBayar = $totalBayarSebelumDiskon - $totalDiskonAmount;

            if ($finalTotalBayar < 0) {
                $finalTotalBayar = 0;
            }

            // Logika baru untuk menentukan apakah pembulatan ke ribuan terdekat harus diterapkan
            // Kondisi untuk pembulatan ke ribuan terdekat:
            // 1. Penyewaan awalnya ditandai sebagai 'is_sepuasnya' (dari database)
            // 2. ATAU Ini adalah paket dengan durasi 0 (yang secara fungsional adalah 'sepuasnya')
            // 3. ATAU 'waktu_selesai' awalnya null saat penyewaan dimuat (menandakan penyewaan terbuka)
            $isSepuasnyaTypeBooking = $penyewaan->is_sepuasnya ||
                                     ($penyewaan->paket_id && $penyewaan->paket && (float)($penyewaan->paket->isi_paket['durasi_jam'] ?? 1) === 0.0) ||
                                     ($originalWaktuSelesai === null);

            if ($isSepuasnyaTypeBooking) {
                // Bulatkan ke ribuan terdekat dan cast ke integer
                $finalTotalBayar = round((float) $finalTotalBayar, -3);
                $finalTotalBayar = (int) $finalTotalBayar;
            } else {
                // Untuk penyewaan durasi tetap standar, bulatkan ke bilangan bulat terdekat (tanpa desimal)
                // karena kolom database adalah DECIMAL(10,0)
                $finalTotalBayar = round((float) $finalTotalBayar, 0);
                $finalTotalBayar = (int) $finalTotalBayar;
            }

            $isQrisPayment = $request->boolean('is_qris', false);
            $penyewaan->update([
                'kasir_id'      => Auth::id(),
                'kode_kupon'    => $kodeKuponDigunakan,
                'diskon_persen' => $diskonPersenKupon,
                'total_bayar'   => $finalTotalBayar, // Nilai ini sudah dibulatkan ke ribuan/bilangan bulat dan menjadi integer
                'status'        => 'dibayar',
                'is_qris'       => $isQrisPayment,
            ]);

            $penyewaan->meja->update(['status' => 'kosong']);


            return response()->json(['message' => 'Pembayaran sukses!', 'total_bayar' => $finalTotalBayar, 'success' => true]);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            Log::error('Error processing payment: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json(['message' => 'Gagal memproses pembayaran. Silakan coba lagi.', 'success' => false], 500);
        }
    }

    public function deletePenyewaan(Request $request, Penyewaan $penyewaan)
    {
        try {
            if (!in_array(Auth::user()->role, ['admin', 'supervisor'])) {
                return response()->json([
                    'message' => 'Akses ditolak. Penghapusan meja hanya bisa dilakukan oleh Admin atau Supervisor.',
                    'success' => false
                ], 403);
            }        

            if ($penyewaan->status !== 'berlangsung' && $penyewaan->status !== 'waktu_habis') {
                return response()->json(['message' => 'Penyewaan tidak dalam status aktif atau waktu habis sehingga tidak dapat dihapus.', 'success' => false], 400);
            }
            if ($penyewaan->meja->status === 'kosong') {
                return response()->json(['message' => 'Meja sudah kosong dan tidak perlu dihapus penyewaannya.', 'success' => false], 400);
            }


            DB::beginTransaction();
            try {
                $serviceDetails = json_decode($penyewaan->service_detail, true) ?: [];
                foreach ($serviceDetails as $srv) {
                    if (isset($srv['id']) && isset($srv['jumlah'])) {
                        if (isset($srv['subtotal']) && (float)$srv['subtotal'] > 0) {
                            $service = Service::lockForUpdate()->find($srv['id']);
                            if ($service) {
                                $service->increment('stok', $srv['jumlah']);
                            }
                        }
                    }
                }

                $penyewaan->delete();
                $penyewaan->meja->update(['status' => 'kosong']);

                DB::commit();
                return response()->json(['message' => 'Penyewaan berhasil dihapus.', 'success' => true]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error deleting penyewaan ' . $penyewaan->id . ': ' . $e->getMessage());
                return response()->json(['message' => 'Gagal menghapus penyewaan. Silakan coba lagi.', 'success' => false], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error deletePenyewaan (outer catch): ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus penyewaan. Silakan coba lagi.', 'success' => false], 500);
        }
    }

    public function validateMember(Request $request)
    {
        $request->validate([
            'kode_member' => 'required|string|max:255',
        ]);

        $member = Member::where('kode_member', $request->kode_member)
                        ->where('status_keanggotaan', 'Aktif')
                        ->where('tanggal_kadaluarsa', '>=', now())
                        ->first();

        if ($member) {
            return response()->json([
                'valid' => true,
                'nama_member' => $member->nama_member,
                'diskon_persen' => $member->diskon_persen,
            ]);
        } else {
            return response()->json([
                'valid' => false,
                'message' => 'Kode Member tidak valid atau sudah kadaluarsa.'
            ], 404);
        }
    }
}