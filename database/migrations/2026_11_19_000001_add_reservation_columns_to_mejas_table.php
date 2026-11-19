<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE mejas ADD COLUMN IF NOT EXISTS reservation_token UUID NULL');
            DB::statement('ALTER TABLE mejas ADD COLUMN IF NOT EXISTS reservation_expires_at TIMESTAMP NULL');
            DB::statement('ALTER TABLE penyewaans ADD COLUMN IF NOT EXISTS reservation_token UUID NULL');

            return;
        }

        if (! Schema::hasColumn('mejas', 'reservation_token')) {
            Schema::table('mejas', function ($table) {
                $table->uuid('reservation_token')->nullable();
            });
        }

        if (! Schema::hasColumn('mejas', 'reservation_expires_at')) {
            Schema::table('mejas', function ($table) {
                $table->timestamp('reservation_expires_at')->nullable();
            });
        }

        if (! Schema::hasColumn('penyewaans', 'reservation_token')) {
            Schema::table('penyewaans', function ($table) {
                $table->uuid('reservation_token')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Columns may have existed before this migration ran, so dropping them
        // here could remove data that this migration did not create.
    }
};
