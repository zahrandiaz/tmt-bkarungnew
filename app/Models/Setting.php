<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// [BARU] Import trait dan LogOptions dari Spatie Activitylog
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Setting extends Model
{
    // [BARU] Gunakan trait LogsActivity
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    // [BARU] Konfigurasi untuk Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty() // Hanya catat jika ada perubahan
            ->setDescriptionForEvent(fn(string $eventName) => "Pengaturan '{$this->key}' telah di-{$eventName}")
            ->useLogName('Setting');
    }
}