<?php
// File: routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\MejaController; // Panggil MejaController dari namespace root
use App\Http\Controllers\HargaSettingController;
use App\Http\Controllers\ServiceController; 
use App\Http\Controllers\KuponController; 
use App\Http\Controllers\PaketController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PemanduController;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'index'])->name('landing');
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [LoginController::class, 'register'])->name('register');
});

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
        // Kita tidak memerlukan method create dan edit secara terpisah karena akan menggunakan modal
        Route::resource('admin/mejas', MejaController::class)->except(['create', 'show', 'edit'])->names('admin.mejas');

        // CRUD service untuk Admin 
        Route::resource('admin/services', ServiceController::class)->except(['create', 'show', 'edit'])->names('admin.services'); // Tambahkan ini

        Route::resource('admin/kupons', KuponController::class)->except(['create', 'show', 'edit'])->names('admin.kupons');

        // ini rute paket
        Route::resource('admin/pakets', PaketController::class)->except(['create', 'show', 'edit'])->names('admin.pakets');

         Route::get('/admin/reports/daily', [LaporanController::class, 'dailyReport'])->name('admin.reports.daily');
    Route::get('/admin/reports/monthly', [LaporanController::class, 'monthlyReport'])->name('admin.reports.monthly');

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
    // Ini mencakup "Kebijakan (pengaturan harga_settings)" untuk Admin dan "Bos Mengelola Harga"
    Route::middleware(['role:admin,bos'])->group(function () {
        Route::get('/harga-settings', [HargaSettingController::class, 'index'])->name('admin.harga_settings.index');
        Route::put('/harga-settings', [HargaSettingController::class, 'update'])->name('admin.harga_settings.update');
    });

    // KASIR AREA
    Route::middleware('role:kasir')->group(function () {
        Route::get('/kasir', [KasirController::class, 'dashboard'])->name('dashboard.kasir');

        Route::post('/kasir/pesan-durasi', [KasirController::class, 'pesanDurasi'])->name('kasir.pesanDurasi');

        Route::post('/kasir/pesan-sepuasnya', [KasirController::class, 'pesanSepuasnya'])->name('kasir.pesanSepuasnya');

        Route::post('/kasir/pesan-paket', [KasirController::class, 'pesanPaket'])->name('kasir.pesanPaket');
        
        Route::get('/kasir/api/penyewaan-aktif', [KasirController::class, 'getPenyewaanAktifJson'])->name('kasir.api.penyewaanAktif');


        Route::post('/kasir/penyewaan/{penyewaan}/add-duration', [KasirController::class, 'addDuration'])->name('kasir.addDuration');


        Route::post('/kasir/penyewaan/{penyewaan}/add-service', [KasirController::class, 'addService'])->name('kasir.addService');

        Route::delete('/kasir/penyewaan/{penyewaan}/remove-service', [KasirController::class, 'removeService'])->name('kasir.removeService');
        
        Route::post('/kasir/penyewaan/{penyewaan}/bayar', [KasirController::class, 'processPayment'])->name('kasir.processPayment');

        // NEW: Rute API untuk validasi kupon
        Route::get('/api/kupon/validate', [KuponController::class, 'validateKupon'])->name('api.kupon.validate');

        // NEW: Rute API untuk mengambil daftar servic
    });

    // Pemandu AREA (Mirip Kasir tapi tanpa pembayaran, hanya pesan dan tambah service)
   // Pemandu AREA (Semua fungsionalitas Kasir kecuali pembayaran)
Route::middleware('role:pemandu')->group(function () {
    Route::get('/pemandu', [PemanduController::class, 'dashboard'])->name('dashboard.pemandu');

    Route::post('/pemandu/pesan-durasi', [PemanduController::class, 'pesanDurasi'])->name('pemandu.pesanDurasi');
    Route::post('/pemandu/pesan-sepuasnya', [PemanduController::class, 'pesanSepuasnya'])->name('pemandu.pesanSepuasnya');
    Route::post('/pemandu/pesan-paket', [PemanduController::class, 'pesanPaket'])->name('pemandu.pesanPaket');

    Route::get('/pemandu/api/penyewaan-aktif', [PemanduController::class, 'getPenyewaanAktifJson'])->name('pemandu.api.penyewaanAktif');

    Route::post('/pemandu/penyewaan/{penyewaan}/add-duration', [PemanduController::class, 'addDuration'])->name('pemandu.addDuration');
    Route::post('/pemandu/penyewaan/{penyewaan}/add-service', [PemanduController::class, 'addService'])->name('pemandu.addService');
    Route::delete('/pemandu/penyewaan/{penyewaan}/remove-service', [PemanduController::class, 'removeService'])->name('pemandu.removeService');
  
});
  // Pemandu juga butuh API untuk daftar service dan paket
    Route::get('/api/services', [ServiceController::class, 'getServicesJson'])->name('api.services'); // Reuse existing API
    Route::get('/api/pakets', [PaketController::class, 'getPaketsJson'])->name('api.pakets');     // Reuse existing API

});