<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- [BARU] Tambahkan ini

class Purchase extends Model
{
    // [BARU] Gunakan trait SoftDeletes
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'purchase_date',
        'total_amount',
        'notes',
    ];

    /**
     * Mendapatkan supplier yang terkait dengan pembelian ini.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Mendapatkan semua detail item untuk pembelian ini.
     */
    public function details(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }
}