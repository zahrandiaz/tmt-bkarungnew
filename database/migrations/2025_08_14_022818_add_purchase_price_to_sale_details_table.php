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
        Schema::table('sale_details', function (Blueprint $table) {
            // Menambahkan kolom baru untuk menyimpan harga beli (HPP) pada saat transaksi penjualan terjadi.
            // Diletakkan setelah kolom 'sale_price' untuk kerapian struktur.
            // Diberi default(0) untuk memastikan data lama tidak error dan perhitungan tetap berjalan.
            $table->decimal('purchase_price', 15, 2)->after('sale_price')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_details', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback.
            $table->dropColumn('purchase_price');
        });
    }
};