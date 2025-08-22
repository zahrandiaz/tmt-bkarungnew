<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// [BARU] Import trait dan LogOptions dari Spatie Activitylog
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StockAdjustment extends Model
{
    // [BARU] Gunakan trait LogsActivity
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'reason',
    ];

    // [BARU] Konfigurasi untuk Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->setDescriptionForEvent(function(string $eventName) {
                // Eager load relasi produk agar nama produk bisa diambil
                $this->load('product');
                $productName = $this->product->name ?? 'N/A';
                $typeText = $this->type === 'penambahan' ? 'Penambahan' : 'Pengurangan';

                return "{$typeText} stok sebanyak {$this->quantity} untuk produk '{$productName}' telah di-{$eventName}";
            })
            ->useLogName('StockAdjustment');
    }

    /**
     * Mendapatkan produk yang terkait dengan penyesuaian stok.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Mendapatkan pengguna yang melakukan penyesuaian stok.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}