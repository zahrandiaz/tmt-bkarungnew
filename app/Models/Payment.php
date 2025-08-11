<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payable_id',
        'payable_type',
        'amount',
        'payment_date',
        'payment_method',
        'attachment_path',
        'notes',
        'user_id',
    ];

    /**
     * Mendapatkan model induk yang dapat dibayar (Sale atau Purchase).
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mendapatkan pengguna yang mencatat pembayaran.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}