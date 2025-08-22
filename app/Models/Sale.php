<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
// [BARU] Import trait dan LogOptions dari Spatie Activitylog
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Sale extends Model
{
    // [BARU] Gunakan trait LogsActivity
    use HasFactory, SoftDeletes, LogsActivity;

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

    // [BARU] Konfigurasi untuk Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable() // Mencatat semua atribut yang ada di $fillable
            ->setDescriptionForEvent(fn(string $eventName) => "Transaksi Penjualan '{$this->invoice_number}' telah di-{$eventName}")
            ->useLogName('Sale'); // Nama log untuk mempermudah filter
    }

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
     * Mendapatkan semua riwayat pembayaran untuk penjualan ini.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * [BARU] Mendapatkan semua data retur untuk penjualan ini.
     */
    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }
}