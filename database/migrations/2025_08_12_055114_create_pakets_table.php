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
        Schema::create('pakets', function (Blueprint $table) {
            $table->id();
            $table->string('nama_paket')->unique();
            $table->decimal('harga_paket', 10, 0); // Harga total paket
            $table->decimal('durasi_jam', 8, 2); // Durasi jam yang termasuk dalam paket
            $table->text('deskripsi')->nullable();
            $table->json('service_detail_paket')->nullable(); // Detail service yang termasuk dalam paket (JSON)
            $table->boolean('aktif')->default(true); // Status aktif/non-aktif paket
            $table->timestamps();
        });

        // Tambahkan kolom paket_id ke tabel penyewaans (opsional, untuk tracking)
        Schema::table('penyewaans', function (Blueprint $table) {
            $table->foreignId('paket_id')->nullable()->constrained('pakets')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyewaans', function (Blueprint $table) {
            $table->dropForeign(['paket_id']);
            $table->dropColumn('paket_id');
        });
        Schema::dropIfExists('pakets');
    }
};