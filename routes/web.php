<?php
// File: routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\MejaController; // Panggil MejaController dari namespace root
use App\Http\Controllers\HargaSettingController;
use App\Http\Controllers\ServiceController; 
use App\Http\Controllers\KuponController; 
use App\Http\Controllers\PaketController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PemanduController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\LampuController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PelayananController;

Route::middleware('guest')->group(function () {
    Route::get('/profile', [ProfilController::class, 'index'])->name('home');
    Route::get('/', [LoginController::class, 'index'])->name('landing');
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [LoginController::class, 'register'])->name('register');
   
});

Route::resource('events', EventController::class);
Route::resource('pelayanan', PelayananController::class);

Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // ADMIN AREA
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboardadmin', [AdminController::class, 'dashboard'])->name('dashboard.admin');

        Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::put('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
        // CRUD Meja untuk Admin (menggunakan MejaController di root)
        Route::resource('admin/mejas', MejaController::class)->except(['create', 'show', 'edit'])->names('admin.mejas');

        // CRUD service untuk Admin 
        Route::resource('admin/services', ServiceController::class)->except(['create', 'show', 'edit'])->names('admin.services'); 
        
        Route::resource('admin/kupons', KuponController::class)->except(['create', 'show', 'edit'])->names('admin.kupons');

        // ini rute paket
        Route::resource('admin/pakets', PaketController::class)->except(['create', 'show', 'edit'])->names('admin.pakets');
    });

    // BOS AREA
    Route::get('/dashboardbos', function () {
        return view('dashboardbos');
    })->name('dashboard.bos')->middleware('role:bos');

    // SUPERVISOR AREA
    Route::get('/dashboardsupervisor', function () {
        return view('supervisor.dashboard');
    })->name('dashboard.supervisor')->middleware('role:supervisor');

    // Rute yang dapat diakses oleh Admin DAN BOS
    Route::middleware(['role:admin|bos'])->group(function () {
        Route::get('/harga-settings', [HargaSettingController::class, 'index'])->name('admin.harga_settings.index');
        Route::put('/harga-settings', [HargaSettingController::class, 'update'])->name('admin.harga_settings.update');
        
        // Rute members, dikelola oleh Admin|Bos
        Route::resource('members', MemberController::class);
        Route::post('members/{member}/extend', [MemberController::class, 'extendMembership'])->name('members.extend');
        
        Route::get('/control', [LampuController::class, 'index'])->name('lampu.index');
    });

    // KASIR AREA (dan Admin/Supervisor yang juga bisa akses fungsi kasir)
    Route::middleware('role:kasir|admin|supervisor')->group(function () {
        Route::get('/kasir', [KasirController::class, 'dashboard'])->name('dashboard.kasir');

        // ==== Rute Pemesanan LAMA (SEKARANG DINONAKTIFKAN/DIHAPUS DARI CONTROLLER) ====
        // Route::post('/kasir/pesan-durasi', [KasirController::class, 'pesanDurasi'])->name('kasir.pesanDurasi');
        // Route::post('/kasir/pesan-sepuasnya', [KasirController::class, 'pesanSepuasnya'])->name('kasir.pesanSepuasnya');
        // Route::post('/kasir/pesan-paket', [KasirController::class, 'pesanPaket'])->name('kasir.pesanPaket');
        // =========================================================================

        // ===== Rute Pemesanan BARU (Revisi 3: Konfirmasi Pemesanan) =====
        Route::post('/kasir/penyewaan/initiate', [KasirController::class, 'initiatePemesanan'])->name('kasir.initiatePemesanan');
        Route::post('/kasir/penyewaan/confirm', [KasirController::class, 'confirmPemesanan'])->name('kasir.confirmPemesanan');
        Route::post('/kasir/meja/{meja}/reset-status', [KasirController::class, 'resetMejaStatus'])->name('kasir.resetMejaStatus');
        // ================================================================

        Route::get('/kasir/api/penyewaan-aktif', [KasirController::class, 'getPenyewaanAktifJson'])->name('kasir.api.penyewaanAktif');

        Route::post('/kasir/penyewaan/{penyewaan}/add-duration', [KasirController::class, 'addDuration'])->name('kasir.addDuration');
        Route::post('/kasir/penyewaan/{penyewaan}/add-service', [KasirController::class, 'addService'])->name('kasir.addService');
        Route::delete('/kasir/penyewaan/{penyewaan}/remove-service', [KasirController::class, 'removeService'])->name('kasir.removeService');
        
        Route::post('/kasir/penyewaan/{penyewaan}/bayar', [KasirController::class, 'processPayment'])->name('kasir.processPayment');
        Route::delete('/kasir/penyewaan/{penyewaan}/delete', [KasirController::class, 'deletePenyewaan'])->name('kasir.penyewaan.delete');

        // ===== Rute Ubah Meja BARU (Revisi 1) =====
        Route::post('/kasir/penyewaan/{penyewaan}/transfer-meja', [KasirController::class, 'transferMeja'])->name('kasir.transferMeja');
        // ===========================================

        // Rute untuk mendapatkan detail perhitungan pembayaran (Revisi 4 & 5)
        Route::get('/kasir/penyewaan/{penyewaan}/calculate-payment', [KasirController::class, 'calculatePaymentDetails'])->name('kasir.api.calculatePaymentDetails');
        
        // Service Order (jika kasir juga menangani layanan langsung, bukan hanya meja)
        Route::get('/kasir/service-order', [ServiceController::class, 'kasirServiceOrderIndex'])->name('kasir.serviceOrderIndex');
        Route::post('/kasir/service-order/process', [ServiceController::class, 'processServiceOrder'])->name('kasir.processServiceOrder');
        Route::put('/kasir/service-transactions/{transaction}/update-status', [ServiceController::class, 'updateServiceTransactionPaymentStatus'])->name('kasir.updateServiceTransactionStatus'); // Untuk bayar nanti
    });

    // Pemandu AREA (Semua fungsionalitas Kasir kecuali pembayaran dan perubahan meja, jika ini diisolasi)
    Route::middleware('role:pemandu|admin|supervisor')->group(function () {
        Route::get('/pemandu', [PemanduController::class, 'dashboard'])->name('dashboard.pemandu');

        // Pemandu juga perlu rute initiate dan confirm pemesanan jika mereka yang memulai
        Route::post('/pemandu/penyewaan/initiate', [PemanduController::class, 'initiatePemesanan'])->name('pemandu.initiatePemesanan');
        Route::post('/pemandu/penyewaan/confirm', [PemanduController::class, 'confirmPemesanan'])->name('pemandu.confirmPemesanan');
        Route::post('/pemandu/meja/{meja}/reset-status', [PemanduController::class, 'resetMejaStatus'])->name('pemandu.resetMejaStatus');
        
        // Pemandu tidak boleh punya rute pembayaran atau delete penyewaan yang sudah dibayar.
        // Pemandu juga tidak punya rute transfer meja.

        Route::get('/pemandu/api/penyewaan-aktif', [PemanduController::class, 'getPenyewaanAktifJson'])->name('pemandu.api.penyewaanAktif');

        Route::post('/pemandu/penyewaan/{penyewaan}/add-duration', [PemanduController::class, 'addDuration'])->name('pemandu.addDuration');
        Route::post('/pemandu/penyewaan/{penyewaan}/add-service', [PemanduController::class, 'addService'])->name('pemandu.addService');
        Route::delete('/pemandu/penyewaan/{penyewaan}/remove-service', [PemanduController::class, 'removeService'])->name('pemandu.removeService');
    });

    // API Umum (dapat diakses oleh peran yang berbeda)
    Route::get('/api/kupon/validate', [KuponController::class, 'validateKupon'])->name('api.kupon.validate');
    Route::get('/api/services', [ServiceController::class, 'getServicesJson'])->name('api.services');
    Route::get('/api/pakets', [PaketController::class, 'getPaketsJson'])->name('api.pakets');
    Route::post('/api/member/validate', [MemberController::class, 'validateMemberCode'])->name('api.member.validate'); // Menggunakan MemberController untuk validasi member
    Route::post('/api/mejas/{meja}/update-status', [MejaController::class, 'updateStatus'])->name('api.mejas.updateStatus');
    Route::post('/api/update-dynamic-price', [HargaSettingController::class, 'updateDynamicPrice'])->name('api.update_dynamic_price');
    
    // Member (ini sudah di grup admin|bos, jadi dipindah saja agar tidak duplikasi)
    // Route::resource('members', MemberController::class);
    // Route::get('/cek-member', [MemberController::class, 'cekMember']); // Cek member mungkin perlu diakses lebih luas? Atau hanya di kasir?

    // Laporan
    Route::get('/laporan/harian', [LaporanController::class, 'harian'])->name('laporan.harian');
    Route::get('/laporan/bulanan', [LaporanController::class, 'bulanan'])->name('laporan.bulanan');
    Route::get('/laporan/tahunan', [LaporanController::class, 'tahunan'])->name('laporan.tahunan');

    Route::get('/panduan-penggunaan', [Controller::class, 'panduanPenggunaan'])->name('panduan');
});