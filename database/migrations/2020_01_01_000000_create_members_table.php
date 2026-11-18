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
        Schema::create('members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama_member');
            $table->string('kode_member', 50)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('no_telepon', 20)->nullable();
            $table->date('tanggal_daftar');
            $table->date('tanggal_kadaluarsa');
            $table->enum('status_keanggotaan', ['aktif', 'nonaktif', 'expired'])->nullable()->default('aktif');
            $table->float('diskon_persen', null, 0)->nullable()->default(0);
            $table->dateTime('last_payment_date')->nullable();
            $table->decimal('last_payment_amount', 10)->nullable()->default(0);
            $table->string('last_payment_method', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
