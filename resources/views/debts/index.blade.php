<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Utang') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Daftar Utang (Pembelian Belum Lunas)</h3>

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">No. Urut</th>
                                    <th scope="col" class="px-6 py-3">Kode Pembelian</th>
                                    <th scope="col" class="px-6 py-3">Nama Supplier</th>
                                    <th scope="col" class="px-6 py-3">Tanggal Pembelian</th>
                                    <th scope="col" class="px-6 py-3 text-right">Total Tagihan</th>
                                    <th scope="col" class="px-6 py-3 text-right">Sudah Dibayar</th>
                                    <th scope="col" class="px-6 py-3 text-right">Sisa Tagihan</th>
                                    <th scope="col" class="px-6 py-3">
                                        <span class="sr-only">Aksi</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($debts as $index => $purchase)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">{{ $debts->firstItem() + $index }}</td>
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $purchase->purchase_code }}
                                        </td>
                                        <td class="px-6 py-4">{{ $purchase->supplier->name }}</td>
                                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y H:i') }}</td>
                                        <td class="px-6 py-4 text-right">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-right">Rp {{ number_format($purchase->total_paid, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-right font-bold text-red-600">
                                            Rp {{ number_format($purchase->total_amount - $purchase->total_paid, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('purchases.show', $purchase->id) }}" class="font-medium text-blue-600 hover:underline">Kelola</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b">
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                            Tidak ada data utang.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $debts->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>