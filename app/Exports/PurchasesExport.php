<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class PurchasesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        $query = Purchase::query()->with('supplier')->withTrashed();

        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }

        return $query->latest();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID Transaksi',
            'No. Nota',
            'Tanggal Transaksi',
            'Nama Supplier',
            'Total Transaksi (Rp)',
            'Status',
        ];
    }

    /**
     * @param mixed $purchase
     * @return array
     */
    public function map($purchase): array
    {
        return [
            $purchase->id,
            $purchase->id,
            $purchase->created_at->format('d-m-Y H:i:s'),
            $purchase->supplier->name ?? 'N/A',
            $purchase->total_amount,
            $purchase->trashed() ? 'Dibatalkan' : 'Selesai',
        ];
    }
}