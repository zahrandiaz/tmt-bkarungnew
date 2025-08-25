<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pembelian</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 0; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header h2 { margin: 0; font-size: 14px; font-weight: normal; }
        .header p { margin: 2px 0; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-table { margin-top: 20px; width: 50%; float: right; }
        .summary-table td { border: none; padding: 4px; }
        .report-info { font-size: 12px; margin-bottom: 15px; }
        tr.details-row > td { padding: 8px; background-color: #f9f9f9; }
        .details-table { width: 100%; }
        .details-table th, .details-table td { border: 1px solid #ccc; padding: 4px; font-size: 9px; }
        .details-table th { background-color: #e9e9e9; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $settings['store_name'] ?? 'Nama Toko' }}</h1>
        <p>{{ $settings['store_address'] ?? 'Alamat Toko' }}</p>
        <p>Telepon: {{ $settings['store_phone'] ?? 'Nomor Telepon' }}</p>
        <hr>
        <h2>Laporan Pembelian</h2>
    </div>

    <div class="report-info">
        Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM YYYY') }} - {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM YYYY') }} <br>
        Dicetak pada: {{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY, HH:mm') }}
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
                {{-- [BARU] Baris untuk Rincian Produk --}}
                <tr class="details-row">
                    <td colspan="6">
                        <table class="details-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Harga Beli</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->details as $detail)
                                <tr>
                                    <td>{{ $detail->product->name }}</td>
                                    <td class="text-center">{{ $detail->quantity }}</td>
                                    <td class="text-right">{{ number_format($detail->purchase_price, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($detail->quantity * $detail->purchase_price, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
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