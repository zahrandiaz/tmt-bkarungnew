<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Arus Kas & Finansial</title>
    <style>
        body { font-family: 'sans-serif'; font-size: 10px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header h2 { margin: 0; font-size: 14px; font-weight: normal; }
        .header p { margin: 2px 0; font-size: 10px; }
        .report-info { font-size: 12px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .summary-table td { font-size: 12px; font-weight: bold; }
        .section-title { font-size: 14px; font-weight: bold; margin-top: 25px; margin-bottom: 10px; }
        .no-data { text-align: center; padding: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $settings['store_name'] ?? 'Nama Toko' }}</h1>
            <p>{{ $settings['store_address'] ?? 'Alamat Toko' }}</p>
            <p>Telepon: {{ $settings['store_phone'] ?? 'Nomor Telepon' }}</p>
            <hr>
            <h2>Laporan Arus Kas & Finansial</h2>
        </div>

        <div class="report-info">
            Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM YYYY') }} - {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM YYYY') }} <br>
            Dicetak pada: {{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY, HH:mm') }}
        </div>

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
                        <td>
                            @if ($inflow->payable)
                                Pembayaran dari {{ $inflow->payable->customer->name ?? 'N/A' }} ({{ $inflow->payable->invoice_number }})
                            @else
                                Pembayaran untuk Penjualan [Telah Dihapus]
                            @endif
                        </td>
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
                        return (object) [ 'date' => $item->payment_date, 'payable' => $item->payable, 'amount' => $item->amount, 'type' => 'purchase' ];
                    })->concat($expenseOutflows->map(function ($item) {
                        return (object) [ 'date' => $item->expense_date, 'payable' => $item, 'amount' => $item->amount, 'type' => 'expense' ];
                    }))->sortByDesc('date');
                @endphp
                @forelse ($allOutflows as $outflow)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($outflow->date)->isoFormat('D MMM Y') }}</td>
                        <td>
                            @if ($outflow->type === 'purchase')
                                @if ($outflow->payable)
                                    Pembayaran ke {{ $outflow->payable->supplier->name ?? 'N/A' }} ({{ $outflow->payable->purchase_code }})
                                @else
                                    Pembayaran untuk Pembelian [Telah Dihapus]
                                @endif
                            @elseif ($outflow->type === 'expense')
                                Biaya: {{ $outflow->payable->name }} ({{ $outflow->payable->category->name }})
                            @endif
                        </td>
                        <td class="text-right">Rp {{ number_format($outflow->amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="no-data">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>