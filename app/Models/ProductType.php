<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- [BARU] Tambahkan ini

class ProductType extends Model
{
    use HasFactory;

    protected $table = 'karung_product_types';
    protected $fillable = ['name'];

    /**
     * [BARU] Mendefinisikan bahwa satu Jenis Produk memiliki banyak Produk.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_type_id');
    }
}