<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// [BARU] Import trait dan LogOptions dari Spatie Activitylog
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PurchaseReturn extends Model
{
    // [BARU] Gunakan trait LogsActivity
    use HasFactory, LogsActivity;

    protected $guarded = [];

    // [BARU] Konfigurasi untuk Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded() // Mencatat semua atribut karena menggunakan $guarded
            ->setDescriptionForEvent(fn(string $eventName) => "Retur Pembelian '{$this->return_code}' telah di-{$eventName}")
            ->useLogName('PurchaseReturn');
    }

    /**
     * Get the purchase associated with the purchase return.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the user who created the purchase return.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the details for the purchase return.
     */
    public function details(): HasMany
    {
        return $this->hasMany(PurchaseReturnDetail::class);
    }
}