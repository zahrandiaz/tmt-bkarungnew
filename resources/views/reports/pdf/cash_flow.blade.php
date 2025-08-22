<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Arus Kas & Finansial</title>
    <style>
        body {
            font-family: 'sans-serif';
            font-size: 10px;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .periode {
            text-align: center;
            font-size: 12px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .summary-table td {
            font-size: 12px;
            font-weight: bold;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 10px;
        }
        .no-data {
            text-align: center;
            padding: 10px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laporan Arus Kas & Finansial</h1>
        <p class="periode">
            Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} - {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}
        </p>

        <div class="section-title">Ringkasan</div>
        <table class="summary-table">
            <tr>
                <td>Total Uang Masuk</td>
                <td class="text-right">Rp {{ number_format($totalInflow, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Uang Keluar</td>
                <td class="text-right">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Arus Kas Bersih</td>
                <td class="text-right">Rp {{ number_format($netCashFlow, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Piutang (Belum Dibayar)</td>
                <td class="text-right">Rp {{ number_format($totalReceivables, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Utang (Belum Dibayar)</td>
                <td class="text-right">Rp {{ number_format($totalPayables, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="section-title">Rincian Uang Masuk (Penerimaan)</div>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inflows as $inflow)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($inflow->payment_date)->isoFormat('D MMM Y') }}</td>
                        <td>Pembayaran dari {{ $inflow->payable->customer->name ?? 'N/A' }} ({{ $inflow->payable->invoice_number }})</td>
                        <td class="text-right">Rp {{ number_format($inflow->amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="no-data">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Rincian Uang Keluar (Pengeluaran)</div>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $allOutflows = $purchaseOutflows->map(function ($item) {
                        return (object) [
                            'date' => $item->payment_date,
                            'description' => 'Pembayaran ke ' . ($item->payable->supplier->name ?? 'N/A') . ' (' . $item->payable->purchase_code . ')',
                            'amount' => $item->amount,
                        ];
                    })->concat($expenseOutflows->map(function ($item) {
                        return (object) [
                            'date' => $item->expense_date,
                            'description' => 'Biaya: ' . $item->name . ' (' . $item->category->name . ')',
                            'amount' => $item->amount,
                        ];
                    }))->sortByDesc('date');
                @endphp
                @forelse ($allOutflows as $outflow)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($outflow->date)->isoFormat('D MMM Y') }}</td>
                        <td>{{ $outflow->description }}</td>
                        <td class="text-right">Rp {{ number_format($outflow->amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="no-data">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Piutang (Penjualan Belum Lunas)</div>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th class="text-right">Sisa Tagihan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($receivables as $sale)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($sale->sale_date)->isoFormat('D MMM Y') }}</td>
                        <td>{{ $sale->customer->name }}</td>
                        <td class="text-right">Rp {{ number_format($sale->total_amount - $sale->total_paid, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="no-data">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Utang (Pembelian Belum Lunas)</div>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th class="text-right">Sisa Tagihan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payables as $purchase)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->isoFormat('D MMM Y') }}</td>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td class="text-right">Rp {{ number_format($purchase->total_amount - $purchase->total_paid, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="no-data">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>