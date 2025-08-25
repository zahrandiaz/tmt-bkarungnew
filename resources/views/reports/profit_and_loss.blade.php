<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Laba Rugi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Laporan</h3>
                        
                        <form action="{{ route('reports.profit-loss') }}" method="GET">
                            <div class="flex items-center space-x-2 mb-4">
                                <a href="{{ route('reports.profit-loss', ['period' => 'today']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'today' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Hari Ini</a>
                                <a href="{{ route('reports.profit-loss', ['period' => 'this_week']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'this_week' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Minggu Ini</a>
                                <a href="{{ route('reports.profit-loss', ['period' => 'this_month']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'this_month' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Bulan Ini</a>
                                <a href="{{ route('reports.profit-loss', ['period' => 'this_year']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'this_year' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Tahun Ini</a>
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
                                    <a href="{{ route('reports.profit-loss.export.csv', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        CSV
                                    </a>
                                    <a href="{{ route('reports.profit-loss.export.pdf', request()->query()) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        PDF
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h3 class="text-sm font-medium text-gray-500">Total Pendapatan</h3>
                                <p class="mt-2 text-3xl font-semibold text-gray-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h3 class="text-sm font-medium text-gray-500">Total HPP (Modal)</h3>
                                <p class="mt-2 text-3xl font-semibold text-gray-900">- Rp {{ number_format($totalCostOfGoods, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                                <h3 class="text-sm font-medium text-blue-700">Laba Kotor</h3>
                                <p class="mt-2 text-3xl font-semibold text-blue-900">Rp {{ number_format($grossProfit, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-red-50 p-6 rounded-lg border border-red-200">
                                <h3 class="text-sm font-medium text-red-700">Total Biaya Operasional</h3>
                                <p class="mt-2 text-2xl font-semibold text-red-900">- Rp {{ number_format($totalExpenses, 0, ',', '.') }}</p>
                                
                                @if($expensesByCategory->isNotEmpty())
                                    <div class="mt-4 pt-4 border-t border-red-200">
                                        <h4 class="text-xs font-semibold text-gray-600 mb-2 uppercase">Rincian Biaya:</h4>
                                        <div class="space-y-1 text-sm">
                                            @foreach($expensesByCategory as $expenseDetail)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-500">{{ $expenseDetail->category->name ?? 'Tanpa Kategori' }}</span>
                                                    <span class="font-medium text-gray-700">Rp {{ number_format($expenseDetail->total_amount, 0, ',', '.') }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="md:col-span-2 p-6 rounded-lg {{ $netProfit >= 0 ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                <h3 class="text-lg font-medium {{ $netProfit >= 0 ? 'text-green-700' : 'text-red-700' }}">Laba Bersih</h3>
                                <p class="mt-2 text-5xl font-bold {{ $netProfit >= 0 ? 'text-green-900' : 'text-red-900' }}">
                                    Rp {{ number_format($netProfit, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-200 text-xs text-gray-500">
                         <p>* Laporan ini hanya menghitung dari transaksi penjualan yang berstatus <strong>Lunas</strong> dalam periode yang dipilih.</p>
                         <p>* HPP (Harga Pokok Penjualan) dihitung secara akurat dari harga beli riil yang tercatat pada setiap item penjualan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>