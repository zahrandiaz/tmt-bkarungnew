<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Transaksi Pembelian') }} #{{ $purchase->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="flex justify-between items-center mb-6">
                        <a href="{{ route('purchases.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">
                            &larr; Kembali ke Daftar
                        </a>
                        
                        <div class="flex items-center space-x-2">
                             <form method="POST" action="{{ route('purchases.cancel', $purchase->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Batalkan
                                </button>
                            </form>

                            <form method="POST" action="{{ route('purchases.destroy', $purchase->id) }}" onsubmit="return confirm('PERINGATAN: Aksi ini akan menghapus data secara permanen dan tidak dapat dibatalkan. Apakah Anda benar-benar yakin?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Hapus Permanen
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 border-b pb-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Supplier</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $purchase->supplier->name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Tanggal Pembelian</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($purchase->purchase_date)->isoFormat('D MMMM YYYY') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Total Pembelian</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ 'Rp ' . number_format($purchase->total_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="mb-6">
                         <h3 class="text-sm font-medium text-gray-500">Catatan</h3>
                         <p class="mt-1 text-gray-700">{{ $purchase->notes ?? 'Tidak ada catatan.' }}</p>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Rincian Produk</h3>
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama Produk</th>
                                    <th scope="col" class="px-6 py-3 text-right">Jumlah</th>
                                    <th scope="col" class="px-6 py-3 text-right">Harga Satuan</th>
                                    <th scope="col" class="px-6 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchase->details as $detail)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $detail->product->name }}</th>
                                    <td class="px-6 py-4 text-right">{{ $detail->quantity }}</td>
                                    <td class="px-6 py-4 text-right">{{ 'Rp ' . number_format($detail->purchase_price, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-right">{{ 'Rp ' . number_format($detail->quantity * $detail->purchase_price, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="font-bold text-gray-900">
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right">Total</td>
                                    <td class="px-6 py-3 text-right">{{ 'Rp ' . number_format($purchase->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>