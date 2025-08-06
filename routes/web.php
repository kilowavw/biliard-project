<?php
// File: routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\MejaController; // Panggil MejaController dari namespace root
use App\Http\Controllers\HargaSettingController; // Panggil HargaSettingController

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
        Route::post('/kasir/pesan', [KasirController::class, 'pesan'])->name('kasir.pesan');
        Route::get('/kasir/api/penyewaan-aktif', [KasirController::class, 'getPenyewaanAktifJson'])->name('kasir.api.penyewaanAktif');

        // Rute untuk menandai waktu_selesai penyewaan (tidak mengubah status jadi 'selesai' lagi)
        Route::post('/kasir/penyewaan/{penyewaan}/finish', [KasirController::class, 'finishPenyewaan'])->name('kasir.finishPenyewaan');

        // Rute untuk memproses pembayaran
        Route::post('/kasir/penyewaan/{penyewaan}/bayar', [KasirController::class, 'processPayment'])->name('kasir.processPayment');

        // Note: Rute API belum ada
        Route::get('/api/kupon/validate', function (Request $request) {
            $kupon = App\Models\Kupon::where('kode', $request->code)
                                    ->where('aktif', true)
                                    ->where(function($query) {
                                        $query->whereNull('kadaluarsa')
                                              ->orWhere('kadaluarsa', '>=', now());
                                    })
                                    ->first();
            if ($kupon) {
                return response()->json(['valid' => true, 'diskon_persen' => $kupon->diskon_persen]);
            }
            return response()->json(['valid' => false, 'message' => 'Kupon tidak valid atau sudah kadaluarsa.'], 404);
        })->name('api.kupon.validate');
    });

});