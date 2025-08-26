<table>
    <thead>
        <tr>
            <th colspan="5" style="font-weight: bold; font-size: 16px;">Laporan Laba Rugi</th>
        </tr>
        <tr>
            <th colspan="5">Periode: {{ \Carbon\Carbon::parse($data['startDate'])->isoFormat('D MMM YYYY') }} - {{ \Carbon\Carbon::parse($data['endDate'])->isoFormat('D MMM YYYY') }}</th>
        </tr>
        <tr><th colspan="5"></th></tr>
    </thead>
    <tbody>
        <!-- Ringkasan -->
        <tr>
            <td style="font-weight: bold;">Total Pendapatan</td>
            <td>{{ $data['totalRevenue'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Total HPP (Modal)</td>
            <td>{{ $data['totalCostOfGoods'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Laba Kotor</td>
            <td>{{ $data['grossProfit'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Total Biaya Operasional</td>
            <td>{{ $data['totalExpenses'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background-color: #f2f2f2;">LABA BERSIH</td>
            <td style="font-weight: bold; background-color: #f2f2f2;">{{ $data['netProfit'] }}</td>
        </tr>
        <tr><td colspan="5"></td></tr>

        <!-- Rincian Pendapatan -->
        <tr>
            <td colspan="5" style="font-weight: bold; background-color: #e2efda;">RINCIAN PENDAPATAN</td>
        </tr>
        <tr>
            <th style="font-weight: bold;">Tanggal</th>
            <th style="font-weight: bold;">Invoice</th>
            <th style="font-weight: bold;">Pelanggan</th>
            <th style="font-weight: bold;">Total Penjualan</th>
            <th style="font-weight: bold;">Laba</th>
        </tr>
        @forelse($data['sales'] as $sale)
        <tr>
            <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}</td>
            <td>{{ $sale->invoice_number }}</td>
            <td>{{ $sale->customer->name }}</td>
            <td>{{ $sale->total_amount }}</td>
            <td>{{ $sale->total_amount - $sale->total_modal }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5">Tidak ada pendapatan pada periode ini.</td>
        </tr>
        @endforelse
        <tr><td colspan="5"></td></tr>

        <!-- Rincian Biaya -->
        <tr>
            <td colspan="5" style="font-weight: bold; background-color: #fce4d6;">RINCIAN BIAYA OPERASIONAL</td>
        </tr>
        <tr>
            <th style="font-weight: bold;">Tanggal</th>
            <th style="font-weight: bold;">Kategori</th>
            <th style="font-weight: bold;" colspan="2">Keterangan</th>
            <th style="font-weight: bold;">Jumlah</th>
        </tr>
        @forelse($data['expenses'] as $expense)
        <tr>
            <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d-m-Y') }}</td>
            <td>{{ $expense->category->name }}</td>
            <td colspan="2">{{ $expense->name }}</td>
            <td>{{ $expense->amount }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5">Tidak ada biaya pada periode ini.</td>
        </tr>
        @endforelse
    </tbody>
</table>