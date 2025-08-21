<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleReturnDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the sale return that owns the detail.
     */
    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class);
    }

    /**
     * Get the product associated with the sale return detail.
     */
    public function product(): BelongsTo
    {
        // Hubungkan ke model Product, tapi secara eksplisit sebutkan nama tabel kustomnya
        return $this->belongsTo(Product::class, 'product_id');
    }
}