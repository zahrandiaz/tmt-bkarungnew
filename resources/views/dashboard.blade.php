<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dasbor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("Selamat Datang Kembali!") }}
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-700">Penjualan Hari Ini (Rp)</h3>
                    <p class="mt-2 text-3xl font-bold text-blue-600">
                        Rp {{ number_format($totalTodaySales, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-700">Transaksi Penjualan (Hari Ini)</h3>
                    <p class="mt-2 text-3xl font-bold text-green-600">
                        {{ $countTodaySales }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-700">Transaksi Pembelian (Hari Ini)</h3>
                    <p class="mt-2 text-3xl font-bold text-yellow-600">
                        {{ $countTodayPurchases }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-700">Total Pelanggan</h3>
                    <p class="mt-2 text-3xl font-bold text-purple-600">
                        {{ $totalCustomers }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-700">Total Produk</h3>
                    <p class="mt-2 text-3xl font-bold text-red-600">
                        {{ $totalProducts }}
                    </p>
                </div>

            </div>
            </div>
    </div>
</x-app-layout>