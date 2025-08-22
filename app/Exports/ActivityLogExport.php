<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ActivityLogExport implements FromCollection, WithHeadings, WithMapping
{
    protected $activities;

    public function __construct(Collection $activities)
    {
        $this->activities = $activities;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->activities;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Waktu',
            'Pengguna',
            'Aktivitas',
        ];
    }

    /**
     * @param mixed $activity
     *
     * @return array
     */
    public function map($activity): array
    {
        return [
            $activity->created_at->format('d M Y, H:i:s'),
            $activity->causer->name ?? 'Sistem',
            $activity->description,
        ];
    }
}