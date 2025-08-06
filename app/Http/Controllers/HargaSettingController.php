<?php

// File: app/Http/Controllers/HargaSettingController.php

namespace App\Http\Controllers;

use App\Models\HargaSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class HargaSettingController extends Controller
{
    /**
     * Display the form for managing price settings.
     */
    public function index()
    {
        // Ambil record harga setting pertama (dan kemungkinan satu-satunya)
        // Jika belum ada, buat record baru dengan nilai default
        $hargaSetting = HargaSetting::firstOrCreate(
            [], // Kriteria pencarian (kosong berarti cari record pertama)
            ['harga_per_jam' => 10000] // Nilai default jika record baru dibuat
        );

        return view('admin.harga_settings.index', compact('hargaSetting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            // Validasi langsung di dalam controller
            $request->validate([
                'harga_per_jam' => 'required|numeric|min:0|max:10000000', // Atur batas max sesuai kebutuhan
            ], [
                'harga_per_jam.required' => 'Harga per jam wajib diisi.',
                'harga_per_jam.numeric' => 'Harga per jam harus berupa angka.',
                'harga_per_jam.min' => 'Harga per jam tidak boleh negatif.',
                'harga_per_jam.max' => 'Harga per jam terlalu besar.',
            ]);

            $hargaSetting = HargaSetting::firstOrCreate(); // Ambil kembali record tunggal
            $hargaSetting->update([
                'harga_per_jam' => $request->harga_per_jam,
            ]);

            return redirect()->route('admin.harga_settings.index')->with('success', 'Harga per jam berhasil diperbarui!');
        } catch (ValidationException $e) {
            // Jika ada error validasi, redirect back dengan error dan old input
            return back()->withErrors($e->errors(), 'updateHarga')->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating harga setting: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui harga per jam. Silakan coba lagi.');
        }
    }
}