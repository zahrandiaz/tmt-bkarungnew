<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Laba Rugi Sederhana') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold mb-4">Filter Laporan</h3>
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
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Filter
                                </button>
                                <a href="{{ route('reports.profit-loss') }}" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-700">Total Pendapatan (Revenue)</h3>
                    <p class="mt-2 text-3xl font-bold text-blue-600">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-700">Total Modal (HPP)</h3>
                    <p class="mt-2 text-3xl font-bold text-red-600">
                        Rp {{ number_format($totalCostOfGoods, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-700">Laba Kotor</h3>
                    <p class="mt-2 text-3xl font-bold {{ $totalProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($totalProfit, 0, ',', '.') }}
                    </p>
                </div>
            </div>
             <div class="mt-4 text-sm text-gray-600">
                <p>* Laporan ini dihitung berdasarkan penjualan yang sudah selesai pada rentang tanggal yang dipilih.</p>
                <p>* HPP (Harga Pokok Penjualan) dihitung dari harga beli produk saat ini di master data.</p>
            </div>
        </div>
    </div>
</x-app-layout>