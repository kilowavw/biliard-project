<?php

namespace App\Console\Commands;

use App\Models\HargaSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateActiveHourlyPrice extends Command
{
    protected $signature = 'price:update-active';
    protected $description = 'Updates the main "harga_per_jam" based on day/night settings.';

    public function handle()
    {
        $settings = HargaSetting::first();

        if (!$settings) {
            $this->error('HargaSetting record not found. Please configure price settings first.');
            Log::error('HargaSetting record not found when trying to update active price.');
            return;
        }

        $currentHour = now()->hour; // Mendapatkan jam saat ini (0-23)

        $newActivePrice = (float) $settings->harga_per_jam; // Default ke harga umum jika tidak cocok
        $period = 'umum'; // Default period

        // Asumsi: Siang dari 06:00 (6) sampai 16:59 (16)
        //         Malam dari 17:00 (17) sampai 05:59 (5)
        if ($currentHour >= 6 && $currentHour < 17) { // 6 AM to 4:59 PM
            $newActivePrice = (float) $settings->harga_per_jam_siang;
            $period = 'siang';
        } else { // 5 PM to 5:59 AM
            $newActivePrice = (float) $settings->harga_per_jam_malam;
            $period = 'malam';
        }

        // Hanya update jika ada perubahan untuk menghindari penulisan database yang tidak perlu
        if ((float) $settings->harga_per_jam !== $newActivePrice) {
            $settings->update(['harga_per_jam' => $newActivePrice]);
            $this->info("Harga per jam aktif diperbarui menjadi Rp " . number_format($newActivePrice, 0, ',', '.') . " untuk periode {$period}.");
            Log::info("Harga per jam aktif diperbarui menjadi {$newActivePrice} untuk periode {$period}.");
        } else {
            $this->comment("Harga per jam aktif sudah sesuai untuk periode {$period} (Rp " . number_format($newActivePrice, 0, ',', '.') . ").");
        }
    }
}