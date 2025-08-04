<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

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

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }
}