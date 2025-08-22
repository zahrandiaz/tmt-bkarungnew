<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// [BARU] Import trait dan LogOptions dari Spatie Activitylog
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Expense extends Model
{
    // [BARU] Gunakan trait LogsActivity
    use HasFactory, LogsActivity;

    protected $fillable = [
        'expense_category_id',
        'name',
        'amount',
        'expense_date',
        'notes',
        'attachment_path',
        'user_id',
    ];

    // [BARU] Konfigurasi untuk Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable() // Mencatat semua atribut yang ada di $fillable
            ->setDescriptionForEvent(fn(string $eventName) => "Biaya '{$this->name}' telah di-{$eventName}")
            ->useLogName('Expense'); // Nama log untuk mempermudah filter
    }

    /**
     * Mendapatkan kategori biaya yang terkait dengan biaya ini.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * Mendapatkan pengguna yang mencatat biaya ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}