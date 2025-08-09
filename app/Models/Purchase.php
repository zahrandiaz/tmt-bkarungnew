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
        'purchase_code',      // <-- [BARU] Tambahkan ini
        'reference_number',   // <-- [BARU] Tambahkan ini
        'supplier_id',
        'user_id',            // <-- [BARU] Tambahkan ini
        'purchase_date',
        'total_amount',
        'notes',
        'invoice_image_path', // <-- [BARU] Tambahkan ini
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