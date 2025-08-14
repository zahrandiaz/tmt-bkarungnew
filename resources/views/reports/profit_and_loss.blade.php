<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Laba Rugi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Form Filter -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold mb-4">Filter Laporan</h3>

                    <!-- Tombol Filter Cepat -->
                    <div class="flex items-center space-x-2 mb-4">
                        <a href="{{ route('reports.profit-loss', ['period' => 'today']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'today' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Hari Ini</a>
                        <a href="{{ route('reports.profit-loss', ['period' => 'this_week']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'this_week' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Minggu Ini</a>
                        <a href="{{ route('reports.profit-loss', ['period' => 'this_month']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'this_month' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Bulan Ini</a>
                        <a href="{{ route('reports.profit-loss', ['period' => 'this_year']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'this_year' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Tahun Ini</a>
                    </div>

                    <!-- Filter Manual -->
                    <form action="{{ route('reports.profit-loss') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" value="{{ $startDate ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                                <input type="date" name="end_date" id="end_date" value="{{ $endDate ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div class="self-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    Filter Manual
                                </button>
                                <a href="{{ route('reports.profit-loss') }}" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="space-y-6">
                {{-- Bagian Pendapatan & HPP --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-gray-700">Total Pendapatan</h3>
                        <p class="mt-2 text-3xl font-bold text-blue-600">
                            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-gray-700">Total HPP</h3>
                        <p class="mt-2 text-3xl font-bold text-orange-600">
                           - Rp {{ number_format($totalCostOfGoods, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-gray-400">
                        <h3 class="text-lg font-semibold text-gray-700">Laba Kotor</h3>
                        <p class="mt-2 text-3xl font-bold {{ $grossProfit >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                            Rp {{ number_format($grossProfit, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
                
                {{-- [MODIFIKASI V1.11.0] Bagian Biaya dengan Rincian --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-gray-700">Total Biaya Operasional</h3>
                        <p class="mt-2 text-3xl font-bold text-red-600">
                           - Rp {{ number_format($totalExpenses, 0, ',', '.') }}
                        </p>
                        
                        {{-- Tabel Rincian Biaya --}}
                        @if($expensesByCategory->isNotEmpty())
                            <div class="mt-4 pt-4 border-t">
                                <h4 class="text-md font-semibold text-gray-600 mb-2">Rincian Biaya:</h4>
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
                    
                    <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-md border-l-4 {{ $netProfit >= 0 ? 'border-green-500' : 'border-red-500' }}">
                        <h3 class="text-xl font-semibold text-gray-700">Laba Bersih</h3>
                        <p class="mt-2 text-4xl font-bold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($netProfit, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
            
             <div class="mt-6 text-sm text-gray-600">
                <p>* Laporan ini hanya menghitung penjualan dengan status <strong>Lunas</strong> pada rentang tanggal yang dipilih.</p>
                <p>* HPP (Harga Pokok Penjualan) dihitung dari harga beli produk saat ini di master data.</p>
            </div>

        </div>
    </div>
</x-app-layout>