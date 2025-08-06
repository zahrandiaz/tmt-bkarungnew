<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- [BARU] Tambahkan ini

class Sale extends Model
{
    // [BARU] Gunakan trait HasFactory dan SoftDeletes
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'sale_date',
        'total_amount',
        'notes',
    ];

    /**
     * Mendapatkan pelanggan yang terkait dengan penjualan ini.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Mendapatkan semua detail item untuk penjualan ini.
     */
    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }
}