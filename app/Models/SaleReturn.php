<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// [BARU] Import trait dan LogOptions dari Spatie Activitylog
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SaleReturn extends Model
{
    // [BARU] Gunakan trait LogsActivity
    use HasFactory, LogsActivity;

    protected $guarded = [];

    // [BARU] Konfigurasi untuk Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded() // Mencatat semua atribut karena menggunakan $guarded
            ->setDescriptionForEvent(fn(string $eventName) => "Retur Penjualan '{$this->return_code}' telah di-{$eventName}")
            ->useLogName('SaleReturn');
    }

    /**
     * Get the sale associated with the sale return.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the user who created the sale return.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the details for the sale return.
     */
    public function details(): HasMany
    {
        return $this->hasMany(SaleReturnDetail::class);
    }
}