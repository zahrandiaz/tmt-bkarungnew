<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
// [BARU] Import trait dan LogOptions dari Spatie Activitylog
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductType extends Model
{
    // [BARU] Gunakan trait LogsActivity
    use HasFactory, LogsActivity;

    protected $table = 'karung_product_types';
    protected $fillable = ['name'];

    // [BARU] Konfigurasi untuk Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->setDescriptionForEvent(fn(string $eventName) => "Jenis Produk '{$this->name}' telah di-{$eventName}")
            ->useLogName('ProductType');
    }

    /**
     * [BARU] Mendefinisikan bahwa satu Jenis Produk memiliki banyak Produk.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_type_id');
    }
}