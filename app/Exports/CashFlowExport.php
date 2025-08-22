<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CashFlowExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $exportData = new Collection();

        // --- Bagian Ringkasan ---
        $exportData->push(['Laporan Arus Kas & Finansial']);
        $exportData->push(['Periode', $this->data['startDate'] . ' s/d ' . $this->data['endDate']]);
        $exportData->push([]); // Baris kosong
        $exportData->push(['Kategori', 'Jumlah']);
        $exportData->push(['Total Uang Masuk', $this->data['totalInflow']]);
        $exportData->push(['Total Uang Keluar', $this->data['totalOutflow']]);
        $exportData->push(['Arus Kas Bersih', $this->data['netCashFlow']]);
        $exportData->push(['Total Piutang (Belum Dibayar)', $this->data['totalReceivables']]);
        $exportData->push(['Total Utang (Belum Dibayar)', $this->data['totalPayables']]);
        $exportData->push([]); // Baris kosong

        // --- Rincian Uang Masuk ---
        $exportData->push(['RINCIAN UANG MASUK']);
        $exportData->push(['Tanggal', 'Keterangan', 'Jumlah']);
        foreach ($this->data['inflows'] as $inflow) {
            $exportData->push([
                'Tanggal' => \Carbon\Carbon::parse($inflow->payment_date)->isoFormat('D MMM Y'),
                'Keterangan' => 'Pembayaran dari ' . ($inflow->payable->customer->name ?? 'N/A') . ' (' . $inflow->payable->invoice_number . ')',
                'Jumlah' => $inflow->amount,
            ]);
        }
        $exportData->push([]); // Baris kosong

        // --- Rincian Uang Keluar ---
        $allOutflows = $this->data['purchaseOutflows']->map(function ($item) {
            return [
                'date' => $item->payment_date,
                'description' => 'Pembayaran ke ' . ($item->payable->supplier->name ?? 'N/A') . ' (' . $item->payable->purchase_code . ')',
                'amount' => $item->amount,
            ];
        })->concat($this->data['expenseOutflows']->map(function ($item) {
            return [
                'date' => $item->expense_date,
                'description' => 'Biaya: ' . $item->name . ' (' . $item->category->name . ')',
                'amount' => $item->amount,
            ];
        }))->sortByDesc('date');

        $exportData->push(['RINCIAN UANG KELUAR']);
        $exportData->push(['Tanggal', 'Keterangan', 'Jumlah']);
        foreach ($allOutflows as $outflow) {
            $exportData->push([
                'Tanggal' => \Carbon\Carbon::parse($outflow['date'])->isoFormat('D MMM Y'),
                'Keterangan' => $outflow['description'],
                'Jumlah' => $outflow['amount'],
            ]);
        }
        $exportData->push([]); // Baris kosong

        // --- Rincian Piutang ---
        $exportData->push(['RINCIAN PIUTANG (PENJUALAN BELUM LUNAS)']);
        $exportData->push(['Tanggal', 'Pelanggan', 'Sisa Tagihan']);
        foreach ($this->data['receivables'] as $sale) {
            $exportData->push([
                'Tanggal' => \Carbon\Carbon::parse($sale->sale_date)->isoFormat('D MMM Y'),
                'Pelanggan' => $sale->customer->name,
                'Sisa Tagihan' => $sale->total_amount - $sale->total_paid,
            ]);
        }
        $exportData->push([]); // Baris kosong

        // --- Rincian Utang ---
        $exportData->push(['RINCIAN UTANG (PEMBELIAN BELUM LUNAS)']);
        $exportData->push(['Tanggal', 'Supplier', 'Sisa Tagihan']);
        foreach ($this->data['payables'] as $purchase) {
            $exportData->push([
                'Tanggal' => \Carbon\Carbon::parse($purchase->purchase_date)->isoFormat('D MMM Y'),
                'Supplier' => $purchase->supplier->name,
                'Sisa Tagihan' => $purchase->total_amount - $purchase->total_paid,
            ]);
        }

        return $exportData;
    }

    public function headings(): array
    {
        // Kita akan menggunakan header kustom di dalam collection(), jadi ini bisa dikosongkan
        // atau dibuat sebagai header utama saja.
        return [
            'A',
            'B',
            'C',
        ];
    }
}