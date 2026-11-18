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
        Schema::create('service_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('kasir_id')->index();
            $table->string('customer_name')->nullable();
            $table->json('service_detail');
            $table->decimal('total_service', 10)->default(0);
            $table->string('kode_kupon')->nullable();
            $table->decimal('diskon_persen', 5)->nullable();
            $table->decimal('diskon_amount', 10)->default(0);
            $table->decimal('total_bayar', 10);
            $table->string('payment_method');
            $table->string('payment_status')->default('paid');
            $table->timestamp('transaction_time')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_transactions');
    }
};
