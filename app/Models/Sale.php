<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany; // <-- TAMBAHKAN INI
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'sale_date',
        'total_amount',
        'payment_method',
        'payment_status',
        'down_payment',
        'total_paid',
        'notes',
        'user_id',
    ];

    /**
     * Mendapatkan pelanggan yang terkait dengan penjualan ini.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Mendapatkan pengguna (staf) yang mencatat penjualan ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan semua detail item untuk penjualan ini.
     */
    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * [BARU] Mendapatkan semua riwayat pembayaran untuk penjualan ini.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}