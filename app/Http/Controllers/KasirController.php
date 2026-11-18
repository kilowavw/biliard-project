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
use Illuminate\Support\Facades\Log;

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
    public function initiatePemesanan(Request $request)
    {
        try {
            $request->validate([
                'meja_id' => 'required|exists:mejas,id',
                'nama_penyewa' => 'required|string|max:255',
                'tipe_pesanan' => 'required|in:durasi,sepuasnya,paket',
                'durasi_jam' => 'nullable|numeric|min:0.01', // Hanya untuk durasi
                'paket_id' => 'nullable|exists:pakets,id', // Hanya untuk paket
                'kode_member' => 'nullable|string|max:255',
            ]);
            $meja = Meja::find($request->meja_id);
            if ($meja->status !== 'kosong') {
                return response()->json(['message' => 'Meja tidak kosong atau sedang tidak tersedia.', 'success' => false], 400);
            }
            DB::beginTransaction();
            try {
                // Update status meja menjadi 'dipakai' untuk "menyalakan lampu"
                $meja->update(['status' => 'dipakai']);
                $memberId = null;
                $diskonMember = 0;
                if ($request->filled('kode_member')) {
                    $member = Member::where('kode_member', $request->kode_member)
                        ->where('status_keanggotaan', 'Aktif')
                        ->where('tanggal_kadaluarsa', '>=', now())
                        ->first();
                    if ($member) {
                        $diskonMember = $member->diskon_persen;
                        $memberId = $member->id;
                    } else {
                        DB::rollBack();
                        return response()->json(['message' => 'Kode Member tidak valid atau sudah kadaluarsa.', 'success' => false], 400);
                    }
                }
                $pemesananData = [
                    'meja_id' => $request->meja_id,
                    'nama_penyewa' => $request->nama_penyewa,
                    'tipe_pesanan' => $request->tipe_pesanan,
                    'kode_member' => $request->kode_member, // Simpan untuk konfirmasi
                    'member_id' => $memberId,
                    'diskon_member_persen' => $diskonMember,
                ];
                if ($request->tipe_pesanan === 'durasi') {
                    $pemesananData['durasi_jam'] = (float) $request->durasi_jam;
                } elseif ($request->tipe_pesanan === 'paket') {
                    $paket = Paket::find($request->paket_id);
                    if (!$paket || !$paket->aktif) {
                        DB::rollBack();
                        throw ValidationException::withMessages(['paket_id' => 'Paket tidak ditemukan atau tidak aktif.']);
                    }
                    $pemesananData['paket_id'] = $request->paket_id;
                    $pemesananData['paket_nama'] = $paket->nama_paket;
                    $pemesananData['paket_details'] = $paket->isi_paket;
                    // Durasi dan is_sepuasnya untuk paket akan ditentukan di tahap konfirmasi
                }
                DB::commit();
                return response()->json(['message' => 'Meja ' . $meja->nama_meja . ' siap dikonfirmasi!', 'success' => true, 'meja_status' => 'dipakai', 'pemesanan_data' => $pemesananData], 200);
            } catch (ValidationException $e) {
                DB::rollBack();
                return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error initiate pemesanan by Kasir: ' . $e->getMessage());
                return response()->json(['message' => 'Gagal memulai proses pemesanan. Silakan coba lagi.', 'success' => false], 500);
            }
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            Log::error('Error initiate pemesanan (outer catch): ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memulai proses pemesanan. Silakan coba lagi.', 'success' => false], 500);
        }
    }
    public function confirmPemesanan(Request $request)
    {
        try {
            $request->validate([
                'meja_id' => 'required|exists:mejas,id',
                'nama_penyewa' => 'required|string|max:255',
                'tipe_pesanan' => 'required|in:durasi,sepuasnya,paket',
                'durasi_jam' => 'nullable|numeric|min:0.01',
                'paket_id' => 'nullable|exists:pakets,id',
                'member_id' => 'nullable|exists:members,id',
                'diskon_member_persen' => 'nullable|numeric',
            ]);
            $meja = Meja::find($request->meja_id);
            if ($meja->status !== 'dipakai') { // Meja harus sudah 'dipakai' dari tahap initiate
                return response()->json(['message' => 'Meja tidak dalam status siap dikonfirmasi.', 'success' => false], 400);
            }
            DB::beginTransaction();
            try {
                $hargaSetting = HargaSetting::latest()->first();
                $hargaPerJam = $hargaSetting ? $hargaSetting->harga_per_jam : 0;
                $durasiJam = 0;
                $hargaPokokSewa = $hargaPerJam; // Default untuk durasi/sepuasnya
                $isSepuasnya = false;
                $waktuSelesai = null;
                $serviceDetailPaket = []; // Inisialisasi sebagai array kosong
                $totalServicePaket = 0;
                $paketId = null;
                if ($request->tipe_pesanan === 'durasi') {
                    $durasiJam = (float) $request->durasi_jam;
                    $waktuSelesai = now()->addMinutes($durasiJam * 60);
                } elseif ($request->tipe_pesanan === 'sepuasnya') {
                    $isSepuasnya = true;
                    // Durasi 0 dan waktu_selesai null menandakan "Main Sepuasnya"
                } elseif ($request->tipe_pesanan === 'paket') {
                    $paket = Paket::find($request->paket_id);
                    if (!$paket || !$paket->aktif) {
                        DB::rollBack();
                        throw ValidationException::withMessages(['paket_id' => 'Paket tidak ditemukan atau tidak aktif.']);
                    }
                    $paketId = $request->paket_id;
                    $paketDetails = $paket->isi_paket; // ini sudah array karena cast di model Paket
                    // Gunakan null coalescing operator untuk akses yang aman
                    $durasiJam = (float) ($paketDetails['durasi_jam'] ?? 0);
                    $hargaPokokSewa = (float) ($paketDetails['harga_paket'] ?? 0); // <-- Harga paket disimpan ke $hargaPokokSewa
                    $isSepuasnya = ($durasiJam === 0.0);
                    $waktuSelesai = $isSepuasnya ? null : now()->addMinutes($durasiJam * 60);
                    $servicesToDecrement = [];
                    // Pastikan $paketDetails['services'] adalah array sebelum diiterasi
                    if (isset($paketDetails['services']) && is_array($paketDetails['services'])) {
                        foreach ($paketDetails['services'] as $srv) {
                            if (isset($srv['id']) && isset($srv['nama']) && isset($srv['jumlah'])) {
                                $serviceDetailPaket[] = [
                                    'id' => (int) $srv['id'],
                                    'nama' => $srv['nama'],
                                    'jumlah' => (int) $srv['jumlah'],
                                    'subtotal' => 0, // Subtotal service dari paket selalu 0
                                ];
                                $servicesToDecrement[] = ['id' => (int) $srv['id'], 'jumlah' => (int) $srv['jumlah'], 'nama' => $srv['nama']];
                            }
                        }
                    }
                    // Dekremen stok service paket
                    foreach ($servicesToDecrement as $srvItem) {
                        $service = Service::lockForUpdate()->find($srvItem['id']);
                        if (!$service) {
                            DB::rollBack();
                            throw ValidationException::withMessages([
                                'paket_id' => "Service '{$srvItem['nama']}' dalam paket tidak ditemukan atau tidak valid."
                            ]);
                        }
                        if ($service->stok < $srvItem['jumlah']) {
                            DB::rollBack();
                            throw ValidationException::withMessages([
                                'paket_id' => "Stok {$service->nama} kurang untuk paket. Tersedia: {$service->stok}."
                            ]);
                        }
                        $service->decrement('stok', $srvItem['jumlah']);
                    }
                    // ServiceDetailPaket sudah menjadi array, tinggal di-encode saat disimpan
                }
                Penyewaan::create([
                    'meja_id' => $request->meja_id,
                    'nama_penyewa' => $request->nama_penyewa,
                    'durasi_jam' => $durasiJam,
                    'harga_per_jam' => $hargaPokokSewa, // <-- Kolom ini sekarang menyimpan harga paket atau harga per jam reguler
                    'kode_kupon' => null, // Kupon diterapkan saat pembayaran
                    'diskon_persen' => 0,    // Diskon kupon diterapkan saat pembayaran
                    'total_service' => $totalServicePaket,
                    'service_detail' => json_encode($serviceDetailPaket), // Encode array ke JSON string
                    'total_bayar' => null,
                    'waktu_mulai' => now(),
                    'waktu_selesai' => $waktuSelesai,
                    'status' => 'berlangsung',
                    'kasir_id' => Auth::id(),
                    'is_sepuasnya' => $isSepuasnya,
                    'member_id' => $request->member_id,
                    'diskon_member_persen' => $request->diskon_member_persen,
                    'paket_id' => $paketId,
                ]);
                DB::commit();
                return response()->json(['message' => 'Pemesanan berhasil dimulai.', 'success' => true], 200);
            } catch (ValidationException $e) {
                DB::rollBack();
                // Kembalikan status meja ke 'kosong' jika konfirmasi gagal
                // Meja seharusnya sudah dipastikan 'dipakai' di awal, jadi reset jika ada kegagalan internal
                $meja->update(['status' => 'kosong']);
                // Penting: Mengembalikan JSON dengan error detail
                return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
            } catch (\Exception $e) {
                DB::rollBack();
                // Pastikan meja kembali kosong jika ada error tak terduga
                $meja->update(['status' => 'kosong']);
                Log::error('Error confirm pemesanan by Kasir: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: . ' . $e->getLine());
                return response()->json(['message' => 'Gagal mengkonfirmasi pemesanan. Silakan coba lagi.', 'success' => false], 500);
            }
        } catch (ValidationException $e) {
            // Ini menangani validasi request awal (sebelum DB::beginTransaction)
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            Log::error('Error confirm pemesanan (outer catch): ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json(['message' => 'Gagal mengkonfirmasi pemesanan. Silakan coba lagi.', 'success' => false], 500);
        }
    }
    public function resetMejaStatus(Request $request, Meja $meja)
    {
        try {
            if ($meja->status === 'dipakai') { // Hanya reset jika memang sedang 'dipakai' oleh initiatePemesanan
                $meja->update(['status' => 'kosong']);
                return response()->json(['message' => 'Status meja berhasil direset.', 'success' => true]);
            }
            return response()->json(['message' => 'Meja tidak dalam status yang dapat direset.', 'success' => false], 400);
        } catch (\Exception $e) {
            Log::error('Error resetting meja status: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mereset status meja. Silakan coba lagi.', 'success' => false], 500);
        }
    }
    // =========================================================================
    // REVISI 1: Fitur Ubah Meja Saat Pemesanan Berlangsung
    // =========================================================================
    public function transferMeja(Request $request, Penyewaan $penyewaan)
    {
        try {
            $request->validate([
                'meja_baru_id' => 'required|exists:mejas,id',
            ]);
            if ($penyewaan->status !== 'berlangsung') {
                return response()->json(['message' => 'Penyewaan tidak aktif.', 'success' => false], 400);
            }
            $mejaLama = $penyewaan->meja;
            $mejaBaru = Meja::find($request->meja_baru_id);
            if (!$mejaBaru) {
                return response()->json(['message' => 'Meja baru tidak ditemukan.', 'success' => false], 404);
            }
            if ($mejaBaru->id === $mejaLama->id) {
                return response()->json(['message' => 'Meja tujuan sama dengan meja saat ini.', 'success' => false], 400);
            }
            if ($mejaBaru->status !== 'kosong') {
                return response()->json(['message' => 'Meja baru tidak kosong.', 'success' => false], 400);
            }
            DB::beginTransaction();
            try {
                // Perbarui meja lama menjadi 'kosong'
                $mejaLama->update(['status' => 'kosong']);
                // Perbarui meja penyewaan
                $penyewaan->update(['meja_id' => $mejaBaru->id]);
                // Perbarui meja baru menjadi 'dipakai'
                $mejaBaru->update(['status' => 'dipakai']);
                // Catat log (opsional)
                Log::info("Penyewaan ID {$penyewaan->id} dipindahkan dari Meja {$mejaLama->nama_meja} ke Meja {$mejaBaru->nama_meja} oleh Kasir ID " . Auth::id());
                DB::commit();
                return response()->json(['message' => 'Meja berhasil dipindahkan!', 'success' => true], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error transferring meja: ' . $e->getMessage());
                return response()->json(['message' => 'Gagal memindahkan meja. Silakan coba lagi.', 'success' => false], 500);
            }
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            Log::error('Error transferMeja (outer catch): ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memindahkan meja. Silakan coba lagi.', 'success' => false], 500);
        }
    }
    // =========================================================================
    // Metode yang ada (tidak diubah signifikan dari solusi sebelumnya)
    // =========================================================================
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
                $effectiveDurasi = (float) $item->durasi_jam;
                $originalServiceDetails = json_decode($item->service_detail, true) ?: [];
                return [
                    'id' => $item->id,
                    'meja_id' => (int) $item->meja_id,
                    'nama_penyewa' => $item->nama_penyewa,
                    'waktu_mulai' => $item->waktu_mulai->toIso8601String(),
                    'waktu_selesai' => $item->waktu_selesai ? $item->waktu_selesai->toIso8601String() : null,
                    'durasi_jam' => $effectiveDurasi,
                    'harga_per_jam_awal' => $item->harga_per_jam, // Tambahkan ini untuk jaga-jaga
                    'meja_nama' => $item->meja->nama_meja,
                    'status_meja_saat_ini' => $item->meja->status,
                    'status' => $item->status,
                    'harga_per_jam' => $effectiveHarga,
                    'total_service' => (float) $item->total_service,
                    'service_detail' => $originalServiceDetails,
                    'is_sepuasnya' => is_null($item->waktu_selesai), // Menggunakan is_null(waktu_selesai) sebagai indikator sepuasnya
                    'paket_id' => $item->paket_id,
                    'member_id' => $item->member_id,
                    'diskon_member_persen' => (float) $item->diskon_member_persen,
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
            // Periksa jika penyewaan adalah paket dengan durasi tetap > 0
            if ($penyewaan->paket_id && $penyewaan->paket) {
                $paketDetails = $penyewaan->paket->isi_paket;
                // Menggunakan null coalescing operator untuk memastikan array key ada
                if (isset($paketDetails['durasi_jam']) && (float) ($paketDetails['durasi_jam'] ?? 1) > 0) {
                    return response()->json(['message' => 'Tidak bisa menambah durasi untuk penyewaan paket dengan durasi tetap.', 'success' => false], 400);
                }
            }
            // Revisi 2 & 4 tidak berlaku untuk penambahan durasi, hanya untuk perhitungan pembayaran.
            // Durasi_jam di sini tetap akumulatif.
            $penyewaan->durasi_jam += (float) $request->additional_durasi_jam;
            $penyewaan->waktu_selesai = $penyewaan->waktu_selesai->addMinutes((float) $request->additional_durasi_jam * 60);
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
                        // Hanya hapus service yang memiliki subtotal > 0 (bukan service paket gratis)
                        // REVISI: Mengembalikan semua stok, terlepas dari subtotal
                        // Ini sesuai dengan rekomendasi sebelumnya agar konsisten dengan pengurangan stok paket.
                        $removedSubtotal += (float) ($detail['subtotal'] ?? 0); // Tetap hitung subtotal yang dihapus
                        $removedJumlah += (int) ($detail['jumlah'] ?? 0);
                        $serviceName = $detail['nama'];
                    } else {
                        $updatedServiceDetail[] = $detail;
                    }
                }
                if ($removedJumlah > 0) { // Cek berdasarkan jumlah yang dihapus, bukan subtotal saja
                    $service = Service::lockForUpdate()->find($serviceToRemoveId);
                    if ($service) {
                        $service->increment('stok', $removedJumlah);
                    }
                } else {
                    DB::rollBack();
                    return response()->json(['message' => 'Service tidak ditemukan atau tidak ada jumlah yang valid untuk dihapus.', 'success' => false], 404);
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
    // =========================================================================
    // Endpoint untuk Menghitung Detail Pembayaran (Frontend Display)
    // REVISI PEMBULATAN HARGA DI SINI
    // =========================================================================
    public function calculatePaymentDetails(Request $request, Penyewaan $penyewaan)
    {
        try {
            $request->validate([
                'kode_kupon' => 'nullable|string|max:255',
                'payment_mode' => 'nullable|in:full_duration,actual_duration', // Tambahan validasi
            ]);
            $paymentMode = $request->input('payment_mode', 'actual_duration'); // Ambil payment_mode dari request
            $tempPenyewaan = clone $penyewaan;
            // Simpan waktu selesai asli dari DB untuk penentuan tipe booking 'sepuasnya'
            $originalWaktuSelesaiDb = $penyewaan->getOriginal('waktu_selesai');
            $originalDurasiJamDb = (float) $penyewaan->getOriginal('durasi_jam'); // Durasi jam yang dipesan awal
            // Waktu saat ini (aktual)
            $waktuSaatIni = now();
            $totalBayarSebelumDiskon = 0;
            $subtotalSewaUntukDisplay = 0;
            $durasiYangDigunakanUntukPerhitunganHarga = 0;
            $hargaPerJamUntukPerhitungan = (float) $tempPenyewaan->harga_per_jam; // <-- Gunakan harga dari DB
            // REVISI 5: Perhitungan Main Sepuasnya Menggunakan HargaSetting Terbaru
            $currentHargaSetting = HargaSetting::latest()->first();
            $hargaPerJamSaatIni = $currentHargaSetting ? $currentHargaSetting->harga_per_jam : 0;
            // Logika penentuan apakah pembulatan ke ribuan terdekat harus diterapkan
            $isSepuasnyaTypeBooking = $penyewaan->is_sepuasnya ||
                ($penyewaan->paket_id && $penyewaan->paket && (float) ($penyewaan->paket->isi_paket['durasi_jam'] ?? 1) === 0.0) ||
                ($originalWaktuSelesaiDb === null);
            // --- Logika Penentuan Waktu Akhir dan Durasi Perhitungan Harga berdasarkan paymentMode ---
            $waktuAkhirPerhitunganDurasi = $waktuSaatIni; // Default: waktu aktual
            if ($paymentMode === 'full_duration' && !$isSepuasnyaTypeBooking) {
                // Jika kasir memilih bayar full durasi, dan ini BUKAN tipe sepuasnya (durasi tetap)
                $waktuAkhirPerhitunganDurasi = $tempPenyewaan->getOriginal('waktu_selesai'); // Gunakan waktu selesai yang dijadwalkan
                $durasiYangDigunakanUntukPerhitunganHarga = $originalDurasiJamDb; // Gunakan durasi yang dipesan
            } elseif ($isSepuasnyaTypeBooking) {
                // Tipe booking "Sepuasnya" (paket durasi 0 atau is_sepuasnya true)
                $actualDurationMinutes = $tempPenyewaan->waktu_mulai->diffInMinutes($waktuSaatIni);
                // *** REVISI PERHITUNGAN is_sepuasnya SESUAI PERMINTAAN KLIEN ***
                if ($actualDurationMinutes <= 90) {
                    $durasiYangDigunakanUntukPerhitunganHarga = 1; // 0-90 menit dihitung 1 jam
                } else {
                    $remainingMinutes = $actualDurationMinutes - 90;
                    if ($remainingMinutes <= 0) {
                        $durasiYangDigunakanUntukPerhitunganHarga = 1;
                    } else {
                        $durasiYangDigunakanUntukPerhitunganHarga = 1 + ceil($remainingMinutes / 60);
                    }
                }
                // ***************************************************************
                // REVISI 5: Untuk booking sepuasnya, selalu ambil harga terbaru
                // Komentari baris di bawah ini agar harga tetap dari DB
                // $hargaPerJamUntukPerhitungan = $hargaPerJamSaatIni;
                $waktuAkhirPerhitunganDurasi = $waktuSaatIni; // Waktu selesai aktual tetap waktu saat ini
            } else {
                // Ini adalah penyewaan durasi tetap, dan payment_mode adalah 'actual_duration' (atau default)
                // Waktu selesai aktual untuk perhitungan adalah waktu saat ini, atau waktu selesai terjadwal jika sudah lewat.
                if ($tempPenyewaan->waktu_selesai && $tempPenyewaan->waktu_selesai->lessThanOrEqualTo($waktuSaatIni)) {
                    $waktuAkhirPerhitunganDurasi = $tempPenyewaan->waktu_selesai;
                } else {
                    $waktuAkhirPerhitunganDurasi = $waktuSaatIni;
                }
                $actualDurationMinutes = $tempPenyewaan->waktu_mulai->diffInMinutes($waktuAkhirPerhitunganDurasi);
                $durasiYangDigunakanUntukPerhitunganHarga = $actualDurationMinutes / 60;
                // KOMENTAR (untuk masa testing): Minimal 1 jam untuk durasi tetap yang sudah lewat waktu
                // if ($actualDurationMinutes > 0 && $durasiYangDigunakanUntukPerhitunganHarga < 1) {
                //     $durasiYangDigunakanUntukPerhitunganHarga = 1;
                // }
            }
            // --- Akhir Logika Penentuan Waktu Akhir dan Durasi Perhitungan Harga ---
            // Update waktu_selesai tempPenyewaan agar sesuai dengan waktu akhir perhitungan durasi
            $tempPenyewaan->waktu_selesai = $waktuAkhirPerhitunganDurasi;
            // Update durasi_jam pada $tempPenyewaan untuk digunakan dalam perhitungan subtotal
            $tempPenyewaan->durasi_jam = $durasiYangDigunakanUntukPerhitunganHarga;
            // Hitung subtotal sewa
            if ($tempPenyewaan->paket_id && $tempPenyewaan->paket) {
                // GANTI: Gunakan harga_per_jam yang disimpan sejak booking untuk paket
                $subtotalSewaUntukDisplay = (float) $tempPenyewaan->harga_per_jam;
            } else {
                $subtotalSewaUntukDisplay = $tempPenyewaan->durasi_jam * $hargaPerJamUntukPerhitungan;
            }
            $totalBayarSebelumDiskon = $subtotalSewaUntukDisplay + (float) $tempPenyewaan->total_service;
            $diskonMemberPersen = (float) $tempPenyewaan->diskon_member_persen;
            $diskonPersenKupon = 0;
            $kodeKuponDigunakan = null;
            if ($request->filled('kode_kupon')) {
                $kupon = Kupon::where('kode', $request->kode_kupon)
                    ->where('aktif', true)
                    ->where(function ($query) {
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
            $totalDiskonAmount = round(($totalBayarSebelumDiskon * $finalDiskonPersen) / 100);
            $finalTotalBayar = $totalBayarSebelumDiskon - $totalDiskonAmount;
            if ($finalTotalBayar < 0) {
                $finalTotalBayar = 0;
            }
            // REVISI PEMBULATAN AKHIR: Bulatkan harga ke ribuan terdekat, selalu dibulatkan ke bawah.
            // Contoh: 7.916,67 menjadi 7.000
            $finalTotalBayar = floor($finalTotalBayar / 1000) * 1000;
            $finalTotalBayar = (int) $finalTotalBayar; // Cast ke integer untuk DECIMAL(10,0) kolom
            return response()->json([
                'success' => true,
                'subtotal_sewa' => (float) $subtotalSewaUntukDisplay,
                'total_service_tambahan' => (float) $tempPenyewaan->total_service,
                'diskon_member_persen' => $diskonMemberPersen,
                'diskon_kupon_persen' => $diskonPersenKupon,
                'total_diskon_amount' => $totalDiskonAmount, // Ini adalah total diskon dalam Rupiah
                'final_total_bayar' => $finalTotalBayar, // SUDAH DIBULATKAN
                'kode_kupon_digunakan' => $kodeKuponDigunakan,
                'calculated_durasi_jam' => (float) $durasiYangDigunakanUntukPerhitunganHarga, // Durasi aktual yang dihitung untuk tampilan/perhitungan
                'calculated_waktu_selesai' => $tempPenyewaan->waktu_selesai ? $tempPenyewaan->waktu_selesai->toIso8601String() : null, // Waktu selesai aktual yang dihitung
                'harga_per_jam_aktual' => $hargaPerJamUntukPerhitungan, // Harga per jam yang dipakai dalam perhitungan
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
                'payment_mode' => 'nullable|in:full_duration,actual_duration', // Tambahan validasi
            ]);
            if ($penyewaan->status !== 'berlangsung' && $penyewaan->status !== 'waktu_habis') {
                return response()->json(['message' => 'Penyewaan tidak dalam status siap dibayar.', 'success' => false], 400);
            }
            if ($penyewaan->meja->status === 'kosong') {
                return response()->json(['message' => 'Meja sudah kosong dan tidak dapat diproses pembayarannya.', 'success' => false], 400);
            }
            DB::beginTransaction();
            try {
                $paymentMode = $request->input('payment_mode', 'actual_duration'); // Ambil payment_mode dari request
                // Simpan waktu selesai asli dari DB untuk penentuan tipe booking 'sepuasnya'
                $originalWaktuSelesaiDb = $penyewaan->getOriginal('waktu_selesai');
                $originalDurasiJamDb = (float) $penyewaan->getOriginal('durasi_jam'); // Durasi jam yang dipesan awal
                // Waktu saat ini (aktual)
                $waktuSaatIni = now();
                $penyewaan->loadMissing('paket');
                $totalBayarSebelumDiskon = 0;
                $subtotalSewaUntukDisplay = 0;
                $durasiYangDigunakanUntukPerhitunganHarga = 0;
                $hargaPerJamUntukPerhitungan = (float) $penyewaan->harga_per_jam; // <-- Gunakan harga dari DB
                // REVISI 5: Perhitungan Main Sepuasnya Menggunakan HargaSetting Terbaru
                $currentHargaSetting = HargaSetting::latest()->first();
                $hargaPerJamSaatIni = $currentHargaSetting ? $currentHargaSetting->harga_per_jam : 0;
                // Logika penentuan apakah pembulatan ke ribuan terdekat harus diterapkan
                $isSepuasnyaTypeBooking = $penyewaan->is_sepuasnya ||
                    ($penyewaan->paket_id && $penyewaan->paket && (float) ($penyewaan->paket->isi_paket['durasi_jam'] ?? 1) === 0.0) ||
                    ($originalWaktuSelesaiDb === null);
                // --- Logika Penentuan Waktu Akhir dan Durasi Perhitungan Harga berdasarkan paymentMode ---
                $waktuAkhirPerhitunganDurasi = $waktuSaatIni; // Default: waktu aktual
                if ($paymentMode === 'full_duration' && !$isSepuasnyaTypeBooking) {
                    // Jika kasir memilih bayar full durasi, dan ini BUKAN tipe sepuasnya (durasi tetap)
                    $waktuAkhirPerhitunganDurasi = $penyewaan->getOriginal('waktu_selesai'); // Gunakan waktu selesai yang dijadwalkan
                    $durasiYangDigunakanUntukPerhitunganHarga = $originalDurasiJamDb; // Gunakan durasi yang dipesan
                } elseif ($isSepuasnyaTypeBooking) {
                    // Tipe booking "Sepuasnya" (paket durasi 0 atau is_sepuasnya true)
                    $actualDurationMinutes = $penyewaan->waktu_mulai->diffInMinutes($waktuSaatIni);
                    // *** REVISI PERHITUNGAN is_sepuasnya SESUAI PERMINTAAN KLIEN ***
                    if ($actualDurationMinutes <= 90) {
                        $durasiYangDigunakanUntukPerhitunganHarga = 1; // 0-90 menit dihitung 1 jam
                    } else {
                        $remainingMinutes = $actualDurationMinutes - 90;
                        if ($remainingMinutes <= 0) {
                            $durasiYangDigunakanUntukPerhitunganHarga = 1;
                        } else {
                            $durasiYangDigunakanUntukPerhitunganHarga = 1 + ceil($remainingMinutes / 60);
                        }
                    }
                    // ***************************************************************
                    // REVISI 5: Untuk booking sepuasnya, selalu ambil harga terbaru
                    // Komentari baris di bawah ini agar harga tetap dari DB
                    // $hargaPerJamUntukPerhitungan = $hargaPerJamSaatIni;
                    $waktuAkhirPerhitunganDurasi = $waktuSaatIni; // Waktu selesai aktual tetap waktu saat ini
                } else {
                    // Ini adalah penyewaan durasi tetap, dan payment_mode adalah 'actual_duration' (atau default)
                    // Waktu selesai aktual untuk perhitungan adalah waktu saat ini, atau waktu selesai terjadwal jika sudah lewat.
                    if ($penyewaan->waktu_selesai && $penyewaan->waktu_selesai->lessThanOrEqualTo($waktuSaatIni)) {
                        $waktuAkhirPerhitunganDurasi = $penyewaan->waktu_selesai;
                    } else {
                        $waktuAkhirPerhitunganDurasi = $waktuSaatIni;
                    }
                    $actualDurationMinutes = $penyewaan->waktu_mulai->diffInMinutes($waktuAkhirPerhitunganDurasi);
                    $durasiYangDigunakanUntukPerhitunganHarga = $actualDurationMinutes / 60;
                    // KOMENTAR (untuk masa testing): Minimal 1 jam untuk durasi tetap yang sudah lewat waktu
                    // if ($actualDurationMinutes > 0 && $durasiYangDigunakanUntukPerhitunganHarga < 1) {
                    //     $durasiYangDigunakanUntukPerhitunganHarga = 1;
                    // }
                }
                // --- Akhir Logika Penentuan Waktu Akhir dan Durasi Perhitungan Harga ---
                // Update durasi_jam pada $penyewaan untuk disimpan ke DB
                $penyewaan->durasi_jam = $durasiYangDigunakanUntukPerhitunganHarga;
                // Update waktu_selesai pada $penyewaan untuk disimpan ke DB
                // PENTING: Jika payment_mode full_duration, waktu_selesai harus kembali ke original yang dipesan.
                if ($paymentMode === 'full_duration' && !$isSepuasnyaTypeBooking) {
                    $penyewaan->waktu_selesai = $originalWaktuSelesaiDb;
                } else {
                    $penyewaan->waktu_selesai = $waktuAkhirPerhitunganDurasi;
                }
                // Hitung subtotal sewa
                if ($penyewaan->paket_id && $penyewaan->paket) {
                    // GANTI: Gunakan harga_per_jam yang disimpan sejak booking untuk paket
                    $subtotalSewaUntukDisplay = (float) $penyewaan->harga_per_jam;
                } else {
                    $subtotalSewaUntukDisplay = $penyewaan->durasi_jam * $hargaPerJamUntukPerhitungan;
                }
                $totalBayarSebelumDiskon = $subtotalSewaUntukDisplay + (float) $penyewaan->total_service;
                $diskonMemberPersen = (float) $penyewaan->diskon_member_persen;
                $diskonPersenKupon = 0;
                $kodeKuponDigunakan = null;
                if ($request->filled('kode_kupon')) {
                    $kupon = Kupon::where('kode', $request->kode_kupon)
                        ->where('aktif', true)
                        ->where(function ($query) {
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
                $totalDiskonAmount = round(($totalBayarSebelumDiskon * $finalDiskonPersen) / 100);
                $finalTotalBayar = $totalBayarSebelumDiskon - $totalDiskonAmount;
                if ($finalTotalBayar < 0) {
                    $finalTotalBayar = 0;
                }
                // REVISI PEMBULATAN AKHIR: Bulatkan harga ke ribuan terdekat, selalu dibulatkan ke bawah.
                // Contoh: 7.916,67 menjadi 7.000
                $finalTotalBayar = floor($finalTotalBayar / 1000) * 1000;
                $finalTotalBayar = (int) $finalTotalBayar; // Cast ke integer untuk DECIMAL(10,0) kolom
                $isQrisPayment = $request->boolean('is_qris', false);
                $penyewaan->update([
                    'kasir_id' => Auth::id(),
                    'kode_kupon' => $kodeKuponDigunakan,
                    'diskon_persen' => $diskonPersenKupon,
                    'total_bayar' => $finalTotalBayar, // Nilai ini sudah dibulatkan ke ribuan dan menjadi integer
                    'status' => 'dibayar',
                    'is_qris' => $isQrisPayment,
                ]);
                $penyewaan->meja->update(['status' => 'kosong']);
                DB::commit();
                return response()->json(['message' => 'Pembayaran sukses!', 'total_bayar' => $finalTotalBayar, 'success' => true]);
            } catch (ValidationException $e) {
                DB::rollBack();
                return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error processing payment: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
                return response()->json(['message' => 'Gagal memproses pembayaran. Silakan coba lagi.', 'success' => false], 500);
            }
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            Log::error('Error processing payment (outer catch): ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
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
                        // Mengembalikan semua stok service yang terpakai, terlepas dari subtotal
                        // Ini akan menangani service paket (subtotal 0) maupun service tambahan (subtotal > 0)
                        $service = Service::lockForUpdate()->find($srv['id']);
                        if ($service) {
                            $service->increment('stok', (int) $srv['jumlah']);
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