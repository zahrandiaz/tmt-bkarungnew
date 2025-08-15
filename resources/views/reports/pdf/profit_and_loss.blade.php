<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 0; font-size: 12px; }
        .header { text-align: center; margin-bottom: 25px; }
        .header h1 { margin: 0; }
        .header p { margin: 5px 0; }
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary-table td { padding: 8px; border-bottom: 1px solid #ddd; }
        .summary-table .label { width: 70%; }
        .summary-table .amount { text-align: right; font-weight: bold; }
        .summary-table .total { font-size: 1.1em; border-top: 2px solid #000; }
        .expenses-table { width: 100%; border-collapse: collapse; }
        .expenses-table th, .expenses-table td { border: 1px solid #000; padding: 6px; text-align: left; }
        .expenses-table th { background-color: #f2f2f2; }
        .expenses-table .amount { text-align: right; }
        .section-title { font-size: 1.2em; font-weight: bold; margin-top: 20px; margin-bottom: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Laba Rugi</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM YYYY') }} - {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM YYYY') }}</p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY, HH:mm') }}</p>
    </div>

    <table class="summary-table">
        <tr>
            <td class="label">Total Pendapatan</td>
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
        <tr class="total">
            <td class="label">Laba Bersih</td>
            <td class="amount">Rp {{ number_format($netProfit, 0, ',', '.') }}</td>
        </tr>
    </table>

    @if($expensesByCategory->isNotEmpty())
        <div class="section-title">Rincian Biaya Operasional</div>
        <table class="expenses-table">
            <thead>
                <tr>
                    <th>Kategori Biaya</th>
                    <th class="amount">Total Biaya (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesByCategory as $expense)
                <tr>
                    <td>{{ $expense->category->name ?? 'Tanpa Kategori' }}</td>
                    <td class="amount">{{ number_format($expense->total_amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>
</html>