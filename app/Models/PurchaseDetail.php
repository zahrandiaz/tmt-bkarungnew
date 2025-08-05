<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'purchase_price',
    ];

    /**
     * Mendapatkan data pembelian (header) yang terkait.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Mendapatkan data produk yang terkait.
     */
    public function product(): BelongsTo
    {
        // Karena nama tabel produk tidak standar ('karung_products'),
        // kita perlu mendefinisikan foreign key secara eksplisit jika diperlukan.
        // Namun, Laravel cukup pintar untuk menanganinya dalam banyak kasus.
        return $this->belongsTo(Product::class);
    }
}