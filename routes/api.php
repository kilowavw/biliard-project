<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LampuController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/kirim-perintah', [LampuController::class, 'kirimPerintah']);
Route::get('/get-perintah-dan-status-meja', [LampuController::class, 'getPerintahDanStatusMeja']);
Route::get('/get-device-status', [LampuController::class, 'getDeviceStatus']);

Route::post('/meja/{meja}/update-status', function (Request $request, App\Models\Meja $meja) {
    $request->validate(['status' => 'required|string|in:kosong,dipakai,waktu_habis']);
    $meja->update(['status' => $request->status]);
    return response()->json(['message' => 'Status meja berhasil diperbarui.']);
});