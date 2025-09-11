<?php

// File: app/Http/Controllers/HargaSettingController.php

namespace App\Http\Controllers;

use App\Models\HargaSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon; // Import Carbon untuk manipulasi waktu

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
            [
                'harga_per_jam'       => 10000, // Default awal
                'harga_per_jam_siang' => 25000,
                'harga_per_jam_malam' => 30000,
            ] // Nilai default jika record baru dibuat
        );

        // Tambahkan logika untuk menentukan harga saat ini berdasarkan waktu server
        $currentHour = Carbon::now()->hour;
        if ($currentHour >= 9 && $currentHour < 17) { // 09:00 - 16:59
            $hargaSetting->current_active_price_type = 'siang';
            $hargaSetting->harga_per_jam = $hargaSetting->harga_per_jam_siang;
        } else { // 17:00 - 24:00 (dan 00:00 - 08:59)
            $hargaSetting->current_active_price_type = 'malam';
            $hargaSetting->harga_per_jam = $hargaSetting->harga_per_jam_malam;
        }

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
                'harga_per_jam_siang' => 'required|numeric|min:0|max:10000000',
                'harga_per_jam_malam' => 'required|numeric|min:0|max:10000000',
            ], [
                'harga_per_jam_siang.required' => 'Harga per jam siang wajib diisi.',
                'harga_per_jam_siang.numeric'  => 'Harga per jam siang harus berupa angka.',
                'harga_per_jam_siang.min'      => 'Harga per jam siang tidak boleh negatif.',
                'harga_per_jam_siang.max'      => 'Harga per jam siang terlalu besar.',
                'harga_per_jam_malam.required' => 'Harga per jam malam wajib diisi.',
                'harga_per_jam_malam.numeric'  => 'Harga per jam malam harus berupa angka.',
                'harga_per_jam_malam.min'      => 'Harga per jam malam tidak boleh negatif.',
                'harga_per_jam_malam.max'      => 'Harga per jam malam terlalu besar.',
            ]);

            $hargaSetting = HargaSetting::firstOrCreate();
            $hargaSetting->update([
                'harga_per_jam_siang' => $request->harga_per_jam_siang,
                'harga_per_jam_malam' => $request->harga_per_jam_malam,
            ]);

            // Setelah memperbarui harga siang/malam, kita juga perlu menyesuaikan harga_per_jam yang aktif
            $currentHour = Carbon::now()->hour;
            if ($currentHour >= 9 && $currentHour < 17) { // 09:00 - 16:59
                $hargaSetting->update(['harga_per_jam' => $request->harga_per_jam_siang]);
            } else { // 17:00 - 24:00 (dan 00:00 - 08:59)
                $hargaSetting->update(['harga_per_jam' => $request->harga_per_jam_malam]);
            }

            return redirect()->route('admin.harga_settings.index')->with('success', 'Harga per jam berhasil diperbarui!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors(), 'updateHarga')->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating harga setting: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui harga per jam. Silakan coba lagi.');
        }
    }

    /**
     * Update the active price dynamically via AJAX based on time.
     */
    public function updateDynamicPrice(Request $request)
    {
        // Pastikan permintaan ini datang dari AJAX dan memiliki token CSRF
        if ($request->ajax() && $request->has('price_type')) {
            $hargaSetting = HargaSetting::firstOrCreate();

            $priceToSet = 0;
            if ($request->price_type === 'siang') {
                $priceToSet = $hargaSetting->harga_per_jam_siang;
            } elseif ($request->price_type === 'malam') {
                $priceToSet = $hargaSetting->harga_per_jam_malam;
            }

            if ($hargaSetting->harga_per_jam !== $priceToSet) {
                $hargaSetting->update(['harga_per_jam' => $priceToSet]);
                Log::info("Harga per jam diperbarui menjadi {$priceToSet} ({$request->price_type}) via JavaScript.");
                return response()->json(['success' => true, 'message' => 'Harga per jam berhasil diperbarui.', 'new_price' => $priceToSet]);
            }

            return response()->json(['success' => false, 'message' => 'Harga per jam sudah sesuai.', 'current_price' => $hargaSetting->harga_per_jam]);
        }

        return response()->json(['success' => false, 'message' => 'Permintaan tidak valid.'], 400);
    }
}