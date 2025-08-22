<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
// [BARU] Import trait dan LogOptions dari Spatie Activitylog
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Customer extends Model
{
    // [BARU] Gunakan trait LogsActivity
    use HasFactory, LogsActivity;

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

    // [BARU] Konfigurasi untuk Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable() // Mencatat semua atribut yang ada di $fillable
            ->setDescriptionForEvent(fn(string $eventName) => "Pelanggan '{$this->name}' telah di-{$eventName}")
            ->useLogName('Customer'); // Nama log untuk mempermudah filter
    }

    /**
     * [NEW] Defines that one Customer has many Sales transactions.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}