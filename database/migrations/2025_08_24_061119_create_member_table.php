<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration (buat tabel).
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
        $table->string('id_member')->primary(); // kode unik contoh MB0987
        $table->string('nama_member');
        $table->string('no_telp')->nullable();
        $table->date('tgl_bergabung');
        $table->timestamps();
    });
    }

    /**
     * Rollback migration (hapus tabel).
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
