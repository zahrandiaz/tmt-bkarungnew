<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
// [BARU] Import trait dan LogOptions dari Spatie Activitylog
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Payment extends Model
{
    // [BARU] Gunakan trait LogsActivity
    use HasFactory, LogsActivity;

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

    // [BARU] Konfigurasi untuk Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            // Membuat deskripsi log yang dinamis tergantung pada jenis pembayaran (Penjualan/Pembelian)
            ->setDescriptionForEvent(function(string $eventName) {
                // Eager load relasi payable agar tidak terjadi N+1 query problem
                $this->load('payable');
                $payableIdentifier = $this->payable->invoice_number ?? $this->payable->purchase_code ?? 'N/A';
                $formattedAmount = number_format($this->amount, 0, ',', '.');

                return "Pembayaran sebesar Rp {$formattedAmount} untuk {$payableIdentifier} telah di-{$eventName}";
            })
            ->useLogName('Payment');
    }

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