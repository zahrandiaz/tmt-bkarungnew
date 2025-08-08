<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- [BARU] Tambahkan ini

class Supplier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'address',
    ];

    /**
     * [BARU] Mendefinisikan bahwa satu Supplier memiliki banyak Transaksi Pembelian.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}