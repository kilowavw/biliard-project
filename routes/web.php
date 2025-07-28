<?php
// File: routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;

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
    });

    // BOS AREA
    Route::get('/dashboardbos', function () {
        return view('dashboardbos');
    })->name('dashboard.bos')->middleware('role:bos');

    // SUPERVISOR AREA
    Route::get('/dashboardsupervisor', function () {
        return view('supervisor.dashboard');
    })->name('dashboard.supervisor')->middleware('role:supervisor');

    // KASIR AREA
    Route::get('/kasir', function () {
        return view('kasir.dashboard');
    })->name('dashboard.kasir')->middleware('role:kasir');
});
