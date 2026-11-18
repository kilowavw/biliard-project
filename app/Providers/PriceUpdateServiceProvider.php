<?php
namespace App\Providers;

use App\Models\HargaSetting; // Asumsikan model Anda bernama HargaSetting
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class PriceUpdateServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Console commands such as package discovery and migrations must be able
        // to run before a database connection or the table exists.
        if ($this->app->runningInConsole() || ! Schema::hasTable('harga_settings')) {
            return;
        }

        // Panggil fungsi pembaruan harga saat aplikasi di-boot
        $this->ensureActivePriceIsUpdatedOnBoot();
    }

    /**
     * Metode helper untuk memperbarui harga saat aplikasi di-boot.
     */
    protected function ensureActivePriceIsUpdatedOnBoot()
    {
        // Ambil setting harga pertama (atau sesuai logika Anda untuk mendapatkan setting yang aktif)
        $hargaSetting = HargaSetting::first(); // Sesuaikan dengan cara Anda mengambil HargaSetting yang relevan

        if ($hargaSetting) {
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
                Log::info("Harga per jam otomatis diperbarui menjadi {$priceToSet} ({$activePriceType}) saat boot aplikasi.");
            } else {
            }

            // Jika Anda menggunakan properti transient untuk view, Anda bisa set di sini juga
            $hargaSetting->current_active_price_type = $activePriceType;
        } else {
            Log::warning("Tidak dapat menemukan HargaSetting untuk diperbarui saat boot aplikasi.");
        }
    }
}
