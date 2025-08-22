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

class Purchase extends Model
{
    // [BARU] Gunakan trait LogsActivity
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'purchase_code',
        'supplier_id',
        'purchase_date',
        'total_amount',
        'payment_method',
        'payment_status',
        'down_payment',
        'total_paid',
        'reference_number',
        'invoice_image_path',
        'notes',
        'user_id',
    ];

    // [BARU] Konfigurasi untuk Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable() // Mencatat semua atribut yang ada di $fillable
            ->setDescriptionForEvent(fn(string $eventName) => "Transaksi Pembelian '{$this->purchase_code}' telah di-{$eventName}")
            ->useLogName('Purchase'); // Nama log untuk mempermudah filter
    }

    /**
     * Mendapatkan supplier yang terkait dengan pembelian ini.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Mendapatkan pengguna (staf) yang mencatat pembelian ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan semua detail item untuk pembelian ini.
     */
    public function details(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    /**
     * Mendapatkan semua riwayat pembayaran untuk pembelian ini.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * [BARU] Mendapatkan semua data retur untuk pembelian ini.
     */
    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }
}