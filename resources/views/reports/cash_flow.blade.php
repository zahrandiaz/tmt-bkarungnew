<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Arus Kas & Finansial') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Filter Form --}}
                    <form action="{{ route('reports.cash-flow') }}" method="GET" class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4 items-end">
                            {{-- Filter Cepat --}}
                            <div>
                                <label for="period" class="block text-sm font-medium text-gray-700">Periode Cepat</label>
                                <select name="period" id="period" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="today" @selected($period == 'today')>Hari Ini</option>
                                    <option value="this_week" @selected($period == 'this_week')>Minggu Ini</option>
                                    <option value="this_month" @selected($period == 'this_month')>Bulan Ini</option>
                                    <option value="this_year" @selected($period == 'this_year')>Tahun Ini</option>
                                </select>
                            </div>
                            {{-- Filter Tanggal Mulai --}}
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ $startDate }}">
                            </div>
                            {{-- Filter Tanggal Akhir --}}
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                                <input type="date" name="end_date" id="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ $endDate }}">
                            </div>
                            {{-- Tombol Filter --}}
                            <div class="flex space-x-2">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Filter
                                </button>
                                <a href="{{ route('reports.cash-flow') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Reset
                                </a>
                            </div>
                            {{-- [BARU] Tombol Ekspor --}}
                            <div class="lg:col-start-5 flex justify-end space-x-2">
                                <button type="submit" name="export" value="csv" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">CSV</button>
                                <button type="submit" name="export" value="pdf" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">PDF</button>
                            </div>
                        </div>
                    </form>
                    
                    <p class="text-sm text-gray-600 mb-6">Menampilkan data dari tanggal <span class="font-semibold">{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }}</span> sampai <span class="font-semibold">{{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</span>.</p>

                    {{-- Ringkasan Statistik Arus Kas --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-2">
                        <div class="bg-green-100 p-4 rounded-lg shadow">
                            <h4 class="text-sm font-semibold text-green-800">Total Uang Masuk</h4>
                            <p class="text-2xl font-bold text-green-900">Rp {{ number_format($totalInflow, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-red-100 p-4 rounded-lg shadow">
                            <h4 class="text-sm font-semibold text-red-800">Total Uang Keluar</h4>
                            <p class="text-2xl font-bold text-red-900">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-blue-100 p-4 rounded-lg shadow">
                            <h4 class="text-sm font-semibold text-blue-800">Arus Kas Bersih</h4>
                            <p class="text-2xl font-bold {{ $netCashFlow < 0 ? 'text-red-900' : 'text-blue-900' }}">
                                Rp {{ number_format($netCashFlow, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    {{-- Ringkasan Statistik Piutang & Utang --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-yellow-100 p-4 rounded-lg shadow">
                            <h4 class="text-sm font-semibold text-yellow-800">Total Piutang (Belum Dibayar)</h4>
                            <p class="text-2xl font-bold text-yellow-900">Rp {{ number_format($totalReceivables, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-orange-100 p-4 rounded-lg shadow">
                            <h4 class="text-sm font-semibold text-orange-800">Total Utang (Belum Dibayar)</h4>
                            <p class="text-2xl font-bold text-orange-900">Rp {{ number_format($totalPayables, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Detail Arus Kas --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800 mb-4">Rincian Uang Masuk (Penerimaan)</h3>
                            <div class="overflow-x-auto border rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse ($inflows as $inflow)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($inflow->payment_date)->isoFormat('D MMM Y') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Pembayaran dari {{ $inflow->payable->customer->name ?? 'N/A' }} ({{ $inflow->payable->invoice_number }})</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">Rp {{ number_format($inflow->amount, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada uang masuk pada periode ini.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800 mb-4">Rincian Uang Keluar (Pengeluaran)</h3>
                            <div class="overflow-x-auto border rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @php
                                            $allOutflows = $purchaseOutflows->map(function ($item) {
                                                $item->date = $item->payment_date;
                                                $item->description = 'Pembayaran ke ' . ($item->payable->supplier->name ?? 'N/A') . ' (' . $item->payable->purchase_code . ')';
                                                return $item;
                                            })->concat($expenseOutflows->map(function ($item) {
                                                $item->date = $item->expense_date;
                                                $item->description = 'Biaya: ' . $item->name . ' (' . $item->category->name . ')';
                                                return $item;
                                            }))->sortByDesc('date');
                                        @endphp
                                        @forelse ($allOutflows as $outflow)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($outflow->date)->isoFormat('D MMM Y') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $outflow->description }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">Rp {{ number_format($outflow->amount, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada uang keluar pada periode ini.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Detail Piutang & Utang --}}
                    <hr class="my-8">
                    <div>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">Catatan Transaksi di Luar Arus Kas</h2>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div>
                                <h3 class="font-semibold text-lg text-gray-800 mb-4">Piutang (Penjualan Belum Lunas)</h3>
                                <div class="overflow-x-auto border rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa Tagihan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse ($receivables as $sale)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($sale->sale_date)->isoFormat('D MMM Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $sale->customer->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">Rp {{ number_format($sale->total_amount - $sale->total_paid, 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada piutang pada periode ini.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg text-gray-800 mb-4">Utang (Pembelian Belum Lunas)</h3>
                                <div class="overflow-x-auto border rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa Tagihan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse ($payables as $purchase)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($purchase->purchase_date)->isoFormat('D MMM Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $purchase->supplier->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">Rp {{ number_format($purchase->total_amount - $purchase->total_paid, 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada utang pada periode ini.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('period').addEventListener('change', function () {
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
        });
        document.getElementById('start_date').addEventListener('change', function () {
            document.getElementById('period').value = '';
        });
        document.getElementById('end_date').addEventListener('change', function () {
            document.getElementById('period').value = '';
        });
    </script>
    @endpush
</x-app-layout>