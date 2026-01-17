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
     * REVISI 2: Pastikan harga_per_jam selalu aktif sesuai waktu saat tampilan.
     */
    public function index()
    {
        $hargaSetting = HargaSetting::firstOrCreate(
            [], // Kriteria pencarian
            [
                'harga_per_jam_siang' => 25000, // Default awal
                'harga_per_jam_malam' => 30000,
            ] // Nilai default jika record baru dibuat
        );

        // Panggil metode untuk menyesuaikan harga aktif saat ini
        $hargaSetting = $this->ensureActivePriceIsUpdated($hargaSetting);

        return view('admin.harga_settings.index', compact('hargaSetting'));
    }

    /**
     * Update the specified resource in storage.
     * REVISI 2: Pastikan harga_per_jam diupdate sesuai waktu saat update setting.
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'harga_per_jam_siang' => 'required|numeric|min:0|max:10000000',
                'harga_per_jam_malam' => 'required|numeric|min:0|max:10000000',
            ], [
                // ... (pesan validasi yang sudah ada)
            ]);

            $hargaSetting = HargaSetting::firstOrCreate();
            $hargaSetting->update([
                'harga_per_jam_siang' => $request->harga_per_jam_siang,
                'harga_per_jam_malam' => $request->harga_per_jam_malam,
            ]);

            // Panggil metode untuk memastikan harga aktif diupdate berdasarkan waktu
            $hargaSetting = $this->ensureActivePriceIsUpdated($hargaSetting);

            return redirect()->route('admin.harga_settings.index')->with('success', 'Harga per jam berhasil diperbarui!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors(), 'updateHarga')->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating harga setting: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui harga per jam. Silakan coba lagi.');
        }
    }

    /**
     * Helper method to ensure `harga_per_jam` is updated based on current time.
     * REVISI 2: Implementasi inti dari perubahan harga otomatis.
     */
    protected function ensureActivePriceIsUpdated(HargaSetting $hargaSetting)
    {
        $currentHour = Carbon::now()->hour;
        $activePriceType = '';
        $priceToSet = 0;

        if ($currentHour >= 9 && $currentHour < 17) { // 09:00 - 16:59
            $activePriceType = 'siang';
            $priceToSet = $hargaSetting->harga_per_jam_siang;
        } else { // 17:00 - 24:00 (dan 00:00 - 08:59)
            $activePriceType = 'malam';
            $priceToSet = $hargaSetting->harga_per_jam_malam;
        }

        // Hanya update jika harga memang berbeda
        if ((float)$hargaSetting->harga_per_jam !== (float)$priceToSet) {
            $hargaSetting->update(['harga_per_jam' => $priceToSet]);
            Log::info("Harga per jam otomatis diperbarui menjadi {$priceToSet} ({$activePriceType}).");
        }

        // Tambahkan properti transient untuk view agar tahu jenis harga yang aktif
        $hargaSetting->current_active_price_type = $activePriceType;
        return $hargaSetting;
    }


    /**
     * Update the active price dynamically via AJAX based on time.
     * REVISI 2: Metode ini sekarang redundan jika ensureActivePriceIsUpdated() digunakan secara konsisten.
     * Bisa dihapus atau dipertahankan untuk override manual/diagnosa.
     * Saya akan menyimpannya sebagai fallback, tapi fokus utama pada ensureActivePriceIsUpdated.
     */
    public function updateDynamicPrice(Request $request)
    {
        Log::info('Permintaan updateDynamicPrice diterima (AJAX).', ['is_ajax' => $request->ajax(), 'price_type_request' => $request->input('price_type')]);

        $hargaSetting = HargaSetting::firstOrCreate();

        // Panggil helper untuk memastikan harga aktif di database sudah benar
        // Ini akan mem-override apa pun yang dikirim dari frontend jika waktu transisi sudah lewat.
        // Jika frontend mengirim 'siang' tapi sebenarnya sudah jam malam, backend akan mengoreksi.
        $hargaSetting = $this->ensureActivePriceIsUpdated($hargaSetting);

        // Setelah memastikan database sudah benar, kembalikan harga aktif yang ada di DB
        return response()->json([
            'success' => true,
            'message' => 'Harga per jam berhasil diperbarui secara otomatis.',
            'new_price' => $hargaSetting->harga_per_jam,
            'current_active_price_type' => $hargaSetting->current_active_price_type // Tambahkan ini untuk info ke frontend
        ]);
    }
}