<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Contoh: 'NodeMCU_Lampu_Area1'
            $table->string('ip_address')->nullable(); // Alamat IP terakhir dari perangkat
            $table->timestamp('last_seen_at')->nullable(); // Kapan terakhir perangkat aktif

            // Kolom baru untuk perintah yang tertunda
            $table->string('pending_command')->nullable(); // Perintah yang menunggu untuk diambil oleh perangkat (misal: 'RESET')
            $table->timestamp('command_sent_at')->nullable(); // Kapan perintah ini dikirim (disimpan)
            $table->timestamp('command_executed_at')->nullable(); // Kapan perangkat melaporkan telah mengambil/menjalankan perintah

            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};