<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
