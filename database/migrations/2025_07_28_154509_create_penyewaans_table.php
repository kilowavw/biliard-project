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
        Schema::create('penyewaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meja_id')->constrained('mejas')->onDelete('cascade');
            $table->string('nama_penyewa');
            $table->float('durasi_jam');
            $table->float('harga_per_jam');

            $table->string('kode_kupon')->nullable();
            $table->float('diskon_persen')->default(0);

            $table->float('total_service')->default(0);
            $table->json('service_detail')->nullable();

            $table->float('total_bayar');
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->enum('status', ['berlangsung', 'selesai', 'dibayar'])->default('berlangsung');

            $table->foreignId('kasir_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyewaans');
    }
};
