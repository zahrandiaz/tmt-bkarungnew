<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; margin: 0; padding: 0; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0; font-size: 11px; }
        .header h2 { margin-top: 15px; font-size: 16px; }
        
        .report-info { font-size: 11px; margin-bottom: 15px; }
        
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .summary-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .summary-table .label { width: 65%; }
        .summary-table .amount { text-align: right; font-weight: bold; }
        .summary-table .total { font-size: 1.1em; border-top: 2px solid #333; }
        .summary-table .grand-total { background-color: #f2f2f2; font-weight: bold; }

        .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .detail-table th, .detail-table td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        .detail-table th { background-color: #f2f2f2; font-weight: bold; }
        .detail-table .amount { text-align: right; }

        .section-title { font-size: 1.2em; font-weight: bold; margin-top: 20px; margin-bottom: 10px; padding: 5px; background-color: #f2f2f2; }
        
        .page-break { page-break-after: always; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #888; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $settings['store_name'] ?? 'Nama Toko' }}</h1>
        <p>{{ $settings['store_address'] ?? 'Alamat Toko' }}</p>
        <p>Telepon: {{ $settings['store_phone'] ?? 'Nomor Telepon' }}</p>
        <hr>
        <h2>Laporan Laba Rugi</h2>
    </div>

    <div class="report-info">
        <strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM YYYY') }} - {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM YYYY') }} <br>
        <strong>Dicetak pada:</strong> {{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY, HH:mm') }}
    </div>

    <div class="section-title">Ringkasan Laba Rugi</div>
    <table class="summary-table">
        <tr>
            <td class="label">Total Pendapatan dari Penjualan</td>
            <td class="amount">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Total HPP (Harga Pokok Penjualan)</td>
            <td class="amount">- Rp {{ number_format($totalCostOfGoods, 0, ',', '.') }}</td>
        </tr>
        <tr class="total">
            <td class="label">Laba Kotor</td>
            <td class="amount">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Total Biaya Operasional</td>
            <td class="amount">- Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
        </tr>
        <tr class="total grand-total">
            <td class="label">LABA BERSIH</td>
            <td class="amount">Rp {{ number_format($netProfit, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="section-title">Rincian Pendapatan</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Invoice</th>
                <th>Pelanggan</th>
                <th class="amount">Total Jual</th>
                <th class="amount">Laba</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
            <tr>
                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}</td>
                <td>{{ $sale->invoice_number }}</td>
                <td>{{ $sale->customer->name }}</td>
                <td class="amount">{{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                <td class="amount">{{ number_format($sale->total_amount - $sale->total_modal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center;">Tidak ada data pendapatan pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Rincian Biaya Operasional</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th class="amount">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr>
                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d-m-Y') }}</td>
                <td>{{ $expense->category->name }}</td>
                <td>{{ $expense->name }}</td>
                <td class="amount">{{ number_format($expense->amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center;">Tidak ada data biaya pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>