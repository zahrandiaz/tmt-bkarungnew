<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class DepositsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $this->endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
    }

    public function query()
    {
        $query = Sale::query()->with(['customer', 'details'])
            ->whereNotNull('id'); // Kondisi awal

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('sale_date', [$this->startDate, $this->endDate]);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'No. Invoice',
            'Tanggal',
            'Pelanggan',
            'Total Modal (Rp)',
        ];
    }

    public function map($sale): array
    {
        $totalModal = $sale->details->sum(function ($detail) {
            return $detail->quantity * $detail->purchase_price;
        });

        return [
            $sale->invoice_number,
            Carbon::parse($sale->sale_date)->isoFormat('D MMM YYYY, HH:mm'),
            $sale->customer->name ?? 'N/A',
            $totalModal,
        ];
    }
}