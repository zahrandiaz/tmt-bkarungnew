<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class SalesExport implements FromQuery, WithHeadings, WithMapping
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
        $query = Sale::query()->with('customer')->withTrashed();

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
            'No. Nota', // Jika Anda punya kolom invoice_number, ganti 'id' di mapping
            'Tanggal Transaksi',
            'Nama Pelanggan',
            'Total Transaksi (Rp)',
            'Status',
        ];
    }

    /**
     * @param mixed $sale
     * @return array
     */
    public function map($sale): array
    {
        return [
            $sale->id,
            $sale->id, // Atau $sale->invoice_number jika ada
            $sale->created_at->format('d-m-Y H:i:s'),
            $sale->customer->name ?? 'N/A',
            $sale->total_amount,
            $sale->trashed() ? 'Dibatalkan' : 'Selesai',
        ];
    }
}