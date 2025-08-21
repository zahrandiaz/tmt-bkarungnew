<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Retur Pembelian #{{ $purchaseReturn->return_code }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <a href="{{ route('purchase-returns.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 mb-3 md:mb-0">
                            &larr; Kembali ke Daftar Retur
                        </a>
                        
                        {{-- Opsi tombol aksi untuk masa depan (misal: cetak, hapus) bisa ditambahkan di sini --}}
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6 border-b pb-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Kode Retur</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $purchaseReturn->return_code }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Referensi Kode Pembelian</h3>
                            <a href="{{ route('purchases.show', $purchaseReturn->purchase->id) }}" class="mt-1 text-lg font-semibold text-blue-600 hover:underline">{{ $purchaseReturn->purchase->purchase_code }}</a>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Tanggal & Waktu Retur</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($purchaseReturn->return_date)->isoFormat('D MMM YYYY, HH:mm') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Dicatat Oleh</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $purchaseReturn->user->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-500">Catatan Retur</h3>
                        <p class="mt-1 text-gray-700">{{ $purchaseReturn->notes ?? 'Tidak ada catatan.' }}</p>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Rincian Produk yang Diretur</h3>
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama Produk</th>
                                    <th scope="col" class="px-6 py-3 text-right">Jumlah Diretur</th>
                                    <th scope="col" class="px-6 py-3 text-right">Harga Satuan</th>
                                    <th scope="col" class="px-6 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchaseReturn->details as $detail)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $detail->product->name }}</th>
                                    <td class="px-6 py-4 text-right">{{ $detail->quantity }}</td>
                                    <td class="px-6 py-4 text-right">{{ 'Rp ' . number_format($detail->unit_price, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-right">{{ 'Rp ' . number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="font-bold text-gray-900">
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right">Total Nilai Retur</td>
                                    <td class="px-6 py-3 text-right">{{ 'Rp ' . number_format($purchaseReturn->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>