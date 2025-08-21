<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the purchase return that owns the detail.
     */
    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    /**
     * Get the product associated with the purchase return detail.
     */
    public function product(): BelongsTo
    {
        // Hubungkan ke model Product, tapi secara eksplisit sebutkan nama tabel kustomnya
        return $this->belongsTo(Product::class, 'product_id');
    }
}