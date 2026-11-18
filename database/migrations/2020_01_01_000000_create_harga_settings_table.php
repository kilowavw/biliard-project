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
        Schema::create('harga_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('harga_per_jam');
            $table->decimal('harga_per_jam_siang', 10)->nullable()->default(10000);
            $table->decimal('harga_per_jam_malam', 10)->nullable()->default(15000);
            $table->decimal('harga_membership', 10)->default(0);
            $table->float('diskon_member_default', null, 0)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_settings');
    }
};
