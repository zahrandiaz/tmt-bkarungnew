<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProfitAndLossExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $exportData = [];

        // Ringkasan Utama
        $exportData[] = ['Ringkasan Laba Rugi'];
        $exportData[] = ['Total Pendapatan', $this->data['totalRevenue']];
        $exportData[] = ['Total HPP', $this->data['totalCostOfGoods']];
        $exportData[] = ['Laba Kotor', $this->data['grossProfit']];
        $exportData[] = ['Total Biaya Operasional', $this->data['totalExpenses']];
        $exportData[] = ['Laba Bersih', $this->data['netProfit']];
        $exportData[] = ['']; // Spasi

        // Rincian Biaya Operasional
        if ($this->data['expensesByCategory']->isNotEmpty()) {
            $exportData[] = ['Rincian Biaya Operasional'];
            $exportData[] = ['Kategori Biaya', 'Total Biaya']; // Header untuk rincian
            foreach ($this->data['expensesByCategory'] as $expense) {
                $exportData[] = [
                    $expense->category->name ?? 'Tanpa Kategori',
                    $expense->total_amount,
                ];
            }
        }

        return $exportData;
    }

    public function headings(): array
    {
        // Kita akan menggunakan baris pertama dari array sebagai judul, jadi headings bisa kosong
        // atau bisa di-set untuk baris pertama saja, tapi FromArray lebih fleksibel di sini.
        return [
            'Deskripsi',
            'Jumlah (Rp)',
        ];
    }
}