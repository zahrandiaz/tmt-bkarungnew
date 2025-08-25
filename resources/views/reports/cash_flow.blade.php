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

                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Laporan</h3>
                        
                        <form action="{{ route('reports.cash-flow') }}" method="GET">
                            <div class="flex items-center space-x-2 mb-4">
                                <a href="{{ route('reports.cash-flow', ['period' => 'today']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'today' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Hari Ini</a>
                                <a href="{{ route('reports.cash-flow', ['period' => 'this_week']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'this_week' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Minggu Ini</a>
                                <a href="{{ route('reports.cash-flow', ['period' => 'this_month']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'this_month' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Bulan Ini</a>
                                <a href="{{ route('reports.cash-flow', ['period' => 'this_year']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'this_year' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Tahun Ini</a>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between space-y-4 sm:space-y-0">
                                <div class="flex flex-col sm:flex-row sm:items-end space-y-4 sm:space-y-0 sm:space-x-4">
                                    <div>
                                        <label for="start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                                        <input type="date" name="start_date" id="start_date" value="{{ $startDate ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="end_date" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                                        <input type="date" name="end_date" id="end_date" value="{{ $endDate ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V10zM15 10a1 1 0 011-1h6a1 1 0 011 1v4a1 1 0 01-1 1h-6a1 1 0 01-1-1v-4z"></path></svg>
                                        Filter
                                    </button>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button type="submit" name="export" value="csv" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        CSV
                                    </button>
                                    <button type="submit" name="export" value="pdf" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        PDF
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <h3 class="text-sm font-medium text-green-700">Total Uang Masuk</h3>
                            <p class="mt-2 text-3xl font-semibold text-green-900">Rp {{ number_format($totalInflow, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-red-50 p-6 rounded-lg border border-red-200">
                            <h3 class="text-sm font-medium text-red-700">Total Uang Keluar</h3>
                            <p class="mt-2 text-3xl font-semibold text-red-900">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                            <h3 class="text-sm font-medium text-blue-700">Arus Kas Bersih</h3>
                            <p class="mt-2 text-3xl font-semibold {{ $netCashFlow < 0 ? 'text-red-900' : 'text-blue-900' }}">
                                Rp {{ number_format($netCashFlow, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                            <h4 class="text-sm font-semibold text-yellow-800">Total Piutang (Belum Dibayar)</h4>
                            <p class="text-xl font-bold text-yellow-900">Rp {{ number_format($totalReceivables, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                            <h4 class="text-sm font-semibold text-orange-800">Total Utang (Belum Dibayar)</h4>
                            <p class="text-xl font-bold text-orange-900">Rp {{ number_format($totalPayables, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800 mb-4">Rincian Uang Masuk</h3>
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
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    @if ($inflow->payable)
                                                        Pembayaran dari {{ $inflow->payable->customer->name ?? 'N/A' }} ({{ $inflow->payable->invoice_number }})
                                                    @else
                                                        <span class="text-gray-400 italic">Pembayaran untuk Penjualan [Telah Dihapus]</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">Rp {{ number_format($inflow->amount, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="px-6 py-12 text-center text-sm text-gray-500">Tidak ada uang masuk.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800 mb-4">Rincian Uang Keluar</h3>
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
                                                return (object) [ 'date' => $item->payment_date, 'payable' => $item->payable, 'amount' => $item->amount, 'type' => 'purchase' ];
                                            })->concat($expenseOutflows->map(function ($item) {
                                                return (object) [ 'date' => $item->expense_date, 'payable' => $item, 'amount' => $item->amount, 'type' => 'expense' ];
                                            }))->sortByDesc('date');
                                        @endphp
                                        @forelse ($allOutflows as $outflow)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($outflow->date)->isoFormat('D MMM Y') }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    @if ($outflow->type === 'purchase')
                                                        @if ($outflow->payable)
                                                            Pembayaran ke {{ $outflow->payable->supplier->name ?? 'N/A' }} ({{ $outflow->payable->purchase_code }})
                                                        @else
                                                            <span class="text-gray-400 italic">Pembayaran untuk Pembelian [Telah Dihapus]</span>
                                                        @endif
                                                    @elseif ($outflow->type === 'expense')
                                                        Biaya: {{ $outflow->payable->name }} ({{ $outflow->payable->category->name }})
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">Rp {{ number_format($outflow->amount, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="px-6 py-12 text-center text-sm text-gray-500">Tidak ada uang keluar.</td></tr>
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
</x-app-layout>