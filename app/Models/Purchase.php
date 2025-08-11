<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_code',
        'supplier_id',
        'purchase_date',
        'total_amount',
        'payment_method',     // DITAMBAHKAN
        'payment_status',     // DITAMBAHKAN
        'down_payment',       // DITAMBAHKAN
        'total_paid',         // DITAMBAHKAN
        'reference_number',
        'invoice_image_path',
        'notes',
        'user_id',
    ];

    /**
     * Mendapatkan supplier yang terkait dengan pembelian ini.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * [BARU] Mendapatkan pengguna (staf) yang mencatat pembelian ini.
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
}