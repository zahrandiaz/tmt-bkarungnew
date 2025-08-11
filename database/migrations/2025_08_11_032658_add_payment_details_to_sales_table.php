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
        Schema::table('sales', function (Blueprint $table) {
            // Menambahkan kolom setelah 'customer_id'
            $table->after('customer_id', function ($table) {
                $table->string('payment_method')->default('tunai');
                $table->string('payment_status')->default('lunas');
                $table->decimal('down_payment', 15, 2)->nullable();
                $table->decimal('total_paid', 15, 2)->default(0);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'down_payment', 'total_paid']);
        });
    }
};
