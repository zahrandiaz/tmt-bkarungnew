<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karung_product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // <-- TAMBAHKAN INI
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karung_product_categories');
    }
};