<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pembelian</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 0; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-table { margin-top: 20px; width: 50%; float: right; }
        .summary-table td { border: none; padding: 4px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Pembelian</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM YYYY') }} - {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM YYYY') }}</p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY, HH:mm') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Kode Pembelian</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th class="text-right">Total Transaksi</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchases as $purchase)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $purchase->purchase_code }}</td>
                    <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->isoFormat('D MMM YYYY, HH:mm') }}</td>
                    <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $purchase->trashed() ? 'Dibatalkan' : 'Selesai' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td><strong>Jumlah Transaksi</strong></td>
            <td class="text-right">{{ number_format($totalTransactions, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total Pengeluaran</strong></td>
            <td class="text-right">Rp {{ number_format($totalExpenditure, 0, ',', '.') }}</td>
        </tr>
    </table>

</body>
</html>