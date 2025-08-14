<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'sale_price',
        'purchase_price', // <-- [TAMBAHKAN BARIS INI]
    ];

    /**
     * Mendapatkan data penjualan (header) yang terkait.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Mendapatkan data produk yang terkait.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}