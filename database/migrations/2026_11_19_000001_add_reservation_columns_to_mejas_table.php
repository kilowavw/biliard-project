<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mejas', function (Blueprint $table) {
            $table->uuid('reservation_token')->nullable()->index();
            $table->timestamp('reservation_expires_at')->nullable()->index();
        });

        Schema::table('penyewaans', function (Blueprint $table) {
            $table->uuid('reservation_token')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('mejas', function (Blueprint $table) {
            $table->dropIndex(['reservation_token']);
            $table->dropIndex(['reservation_expires_at']);
            $table->dropColumn(['reservation_token', 'reservation_expires_at']);
        });

        Schema::table('penyewaans', function (Blueprint $table) {
            $table->dropIndex(['reservation_token']);
            $table->dropColumn('reservation_token');
        });
    }
};
