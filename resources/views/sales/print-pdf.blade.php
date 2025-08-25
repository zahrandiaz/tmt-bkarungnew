<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $sale->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header, .footer { text-align: center; }
        .header h1 { margin: 0; }
        .header p { margin: 5px 0; }
        .details-section { margin-top: 20px; }
        .details-section table { width: 100%; }
        .invoice-to { float: left; width: 50%; }
        .invoice-details { float: right; width: 50%; text-align: right; }
        .items-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; }
        .items-table th { background-color: #f2f2f2; text-align: left; }
        .text-right { text-align: right; }
        .total-section { margin-top: 20px; float: right; width: 40%; }
        .total-section table { width: 100%; }
        .total-section td { padding: 5px; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .status { font-size: 14px; font-weight: bold; }
        .status-selesai { color: green; }
        .status-dibatalkan { color: red; }
        .footer-notes { text-align: center; margin-top: 40px; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            {{-- [DIPERBARUI] Data dinamis dari settings --}}
            <h1>INVOICE</h1>
            <p><strong>{{ $settings['store_name'] ?? 'Nama Toko' }}</strong></p>
            <p>{{ $settings['store_address'] ?? 'Alamat Toko' }}</p>
        </div>
        <hr>
        <div class="details-section clearfix">
            <div class="invoice-to">
                <h3>Ditagihkan Kepada:</h3>
                <p>{{ $sale->customer->name }}</p>
                <p>{{ $sale->customer->address ?? 'Alamat tidak tersedia' }}</p>
            </div>
            <div class="invoice-details">
                <p><strong>No. Invoice:</strong> {{ $sale->invoice_number }}</p>
                <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->isoFormat('D MMMM YYYY') }}</p>
                <p class="status {{ $sale->trashed() ? 'status-dibatalkan' : 'status-selesai' }}">
                    <strong>Status:</strong> {{ $sale->trashed() ? 'DIBATALKAN' : 'SELESAI' }}
                </p>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Deskripsi Produk</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->details as $detail)
                    <tr>
                        <td>{{ $detail->product->name }}</td>
                        <td class="text-right">{{ $detail->quantity }}</td>
                        <td class="text-right">Rp {{ number_format($detail->sale_price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($detail->quantity * $detail->sale_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>TOTAL</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</strong></td>
                </tr>
            </table>
        </div>
        
        {{-- [BARU] Catatan kaki dinamis --}}
        <div class="footer-notes">
            <p>{{ $settings['invoice_footer_notes'] ?? 'Terima kasih!' }}</p>
        </div>
    </div>
</body>
</html>