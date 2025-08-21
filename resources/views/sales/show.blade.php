<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Transaksi Penjualan #{{ $sale->invoice_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 mb-3 md:mb-0">
                            &larr; Kembali
                        </a>
                        
                        <div class="flex items-center space-x-2">
                            @if (!$sale->trashed())
                                <a href="{{ route('sales.printThermal', $sale->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">Cetak Struk</a>
                                <a href="{{ route('sales.downloadPDF', $sale->id) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500">Unduh PDF</a>
                                @can('transaction-cancel')
                                <form method="POST" action="{{ route('sales.cancel', $sale->id) }}" onsubmit="return confirm('Yakin ingin membatalkan transaksi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500">Batalkan</button>
                                </form>
                                @endcan
                            @endif
                            @can('transaction-delete-permanent')
                                <form method="POST" action="{{ route('sales.destroy', $sale->id) }}" onsubmit="return confirm('PERINGATAN: Aksi ini akan menghapus data permanen. Yakin?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">Hapus Permanen</button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">No. Invoice</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $sale->invoice_number }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status Transaksi</h3>
                            <p class="mt-1 text-lg font-semibold">
                                @if ($sale->trashed())
                                    <span class="px-3 py-1 text-sm font-semibold leading-tight text-red-700 bg-red-100 rounded-full">Dibatalkan</span>
                                @else
                                    <span class="px-3 py-1 text-sm font-semibold leading-tight text-blue-700 bg-blue-100 rounded-full">Selesai</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Tanggal & Waktu</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($sale->sale_date)->isoFormat('D MMM YYYY, HH:mm') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Dicatat Oleh</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $sale->user->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6 border-b pb-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Pelanggan</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $sale->customer->name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status Pembayaran</h3>
                            <p class="mt-1 text-lg font-semibold">
                                @if ($sale->payment_status == 'Lunas')
                                    <span class="px-3 py-1 text-sm font-semibold leading-tight text-green-700 bg-green-100 rounded-full">Lunas</span>
                                @elseif ($sale->payment_status == 'Belum Lunas')
                                    <span class="px-3 py-1 text-sm font-semibold leading-tight text-yellow-700 bg-yellow-100 rounded-full">Belum Lunas</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Metode Pembayaran</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $sale->payment_method }}</p>
                        </div>
                    </div>

                    <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-500">Catatan</h3>
                            <p class="mt-1 text-gray-700">{{ $sale->notes ?? 'Tidak ada catatan.' }}</p>
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
                                @foreach ($sale->details as $detail)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $detail->product->name }}</th>
                                    <td class="px-6 py-4 text-right">{{ $detail->quantity }}</td>
                                    <td class="px-6 py-4 text-right">{{ 'Rp ' . number_format($detail->sale_price, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-right">{{ 'Rp ' . number_format($detail->quantity * $detail->sale_price, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="font-bold text-gray-900">
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right">Total Penjualan</td>
                                    <td class="px-6 py-3 text-right">{{ 'Rp ' . number_format($sale->total_amount, 0, ',', '.') }}</td>
                                </tr>
                                @if($sale->payment_status == 'Belum Lunas')
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right">Uang Muka (DP)</td>
                                    <td class="px-6 py-3 text-right">{{ 'Rp ' . number_format($sale->down_payment, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right">Sisa Tagihan</td>
                                    <td class="px-6 py-3 text-right">{{ 'Rp ' . number_format($sale->total_amount - $sale->total_paid, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>

                    @if($sale->returns->isNotEmpty())
                    <div class="mt-8 pt-6 border-t">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Retur Transaksi Ini</h3>
                        <div class="space-y-4">
                            @foreach($sale->returns as $return)
                            <div class="p-4 border rounded-lg">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $return->return_code }}</p>
                                        <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($return->return_date)->isoFormat('D MMM YYYY, HH:mm') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-red-600">- {{ 'Rp ' . number_format($return->total_amount, 0, ',', '.') }}</p>
                                        <a href="{{ route('sale-returns.show', $return->id) }}" class="text-sm text-blue-600 hover:underline">Detail Retur</a>
                                    </div>
                                </div>
                                <div class="mt-2 text-sm text-gray-600">
                                    <ul class="list-disc pl-5">
                                        @foreach($return->details as $detail)
                                            <li>{{ $detail->product->name }} ({{ $detail->quantity }} pcs)</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>