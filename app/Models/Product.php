<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// [BARU] Import trait dan LogOptions dari Spatie Activitylog
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    // [BARU] Gunakan trait LogsActivity
    use HasFactory, LogsActivity;

    protected $table = 'karung_products';

    protected $fillable = [
        'sku',
        'name',
        'product_category_id',
        'product_type_id',
        'description',
        'purchase_price',
        'selling_price',
        'stock',
        'min_stock_level',
        'image_path',
        'is_active',
    ];

    // [BARU] Konfigurasi untuk Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable() // Mencatat semua atribut yang ada di $fillable
            ->setDescriptionForEvent(fn(string $eventName) => "Produk '{$this->name}' telah di-{$eventName}")
            ->useLogName('Product'); // Nama log untuk mempermudah filter
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }
}