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
        Schema::table('purchases', function (Blueprint $table) {
            // Mengubah tipe kolom menjadi DATETIME
            $table->dateTime('purchase_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Mengembalikan tipe kolom menjadi DATE jika migrasi di-rollback
            $table->date('purchase_date')->change();
        });
    }
};