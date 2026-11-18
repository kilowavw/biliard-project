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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('meja_id')->index();
            $table->string('nama_penyewa');
            $table->decimal('durasi_jam', 8, 4);
            $table->decimal('harga_per_jam', 10, 0);
            $table->string('kode_kupon')->nullable();
            $table->decimal('diskon_persen', 10, 0)->nullable()->default(0);
            $table->boolean('is_qris')->default(false);
            $table->decimal('total_service', 10, 0)->nullable()->default(0);
            $table->json('service_detail')->nullable();
            $table->decimal('total_bayar', 10, 0)->nullable();
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->enum('status', ['berlangsung', 'selesai', 'dibayar'])->default('berlangsung');
            $table->unsignedBigInteger('kasir_id')->nullable()->index();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->decimal('diskon_member_persen', 5)->default(0);
            $table->bigInteger('pemandu_id')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('paket_id')->nullable()->index();
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
