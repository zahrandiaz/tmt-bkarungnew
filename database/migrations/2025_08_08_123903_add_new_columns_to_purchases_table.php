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
            $table->string('purchase_code')->unique()->after('id');
            $table->string('reference_number')->nullable()->after('purchase_code');
            $table->string('invoice_image_path')->nullable()->after('notes');
            $table->foreignId('user_id')->constrained()->after('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'purchase_code',
                'reference_number',
                'invoice_image_path',
                'user_id'
            ]);
        });
    }
};