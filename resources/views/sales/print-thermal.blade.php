<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Penjualan - {{ $sale->invoice_number }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 10pt; color: #000; margin: 0; padding: 0; }
        .receipt { width: 58mm; padding: 1mm; }
        .header { text-align: center; }
        .header h1 { font-size: 12pt; margin: 0; }
        .header p { margin: 1mm 0; font-size: 8pt; }
        .details, .items, .footer { margin-top: 3mm; }
        .details table, .items table { width: 100%; border-collapse: collapse; }
        .items table th, .items table td { padding: 1mm 0; }
        .items .item-name { word-break: break-all; }
        .text-right { text-align: right; }
        .total { font-weight: bold; }
        hr { border: none; border-top: 1px dashed #000; margin: 2mm 0; }
        .footer { text-align: center; font-size: 8pt; }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt">
        <div class="header">
            <h1>TMT Bagja Karung</h1>
            <p>Jl. Contoh No. 123, Kota Anda</p>
            <p>Telp: 0812-3456-7890</p>
        </div>
        <hr>
        <div class="details">
            <table>
                <tr>
                    <td>No:</td>
                    <td class="text-right">{{ $sale->invoice_number }}</td>
                </tr>
                <tr>
                    <td>Tanggal:</td>
                    <td class="text-right">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/y H:i') }}</td>
                </tr>
                <tr>
                    <td>Kasir:</td>
                    <td class="text-right">{{ $sale->user->name ?? 'N/A' }}</td>
                </tr>
                 <tr>
                    <td>Pelanggan:</td>
                    <td class="text-right">{{ $sale->customer->name }}</td>
                </tr>
            </table>
        </div>
        <hr>
        <div class="items">
            <table>
                <tbody>
                    @foreach ($sale->details as $detail)
                        <tr>
                            <td colspan="2" class="item-name">{{ $detail->product->name }}</td>
                        </tr>
                        <tr>
                            <td>{{ $detail->quantity }} x {{ number_format($detail->sale_price, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($detail->quantity * $detail->sale_price, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <hr>
        <div class="footer-summary">
             <table>
                <tr class="total">
                    <td>TOTAL</td>
                    <td class="text-right">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
        <hr>
        <div class="footer">
            <p>Terima kasih telah berbelanja!</p>
        </div>
    </div>
</body>
</html>