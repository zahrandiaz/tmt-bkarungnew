<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// [BARU] Import trait dan LogOptions dari Spatie Activitylog
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ExpenseCategory extends Model
{
    // [BARU] Gunakan trait LogsActivity
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'description',
    ];

    // [BARU] Konfigurasi untuk Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->setDescriptionForEvent(fn(string $eventName) => "Kategori Biaya '{$this->name}' telah di-{$eventName}")
            ->useLogName('ExpenseCategory');
    }
}