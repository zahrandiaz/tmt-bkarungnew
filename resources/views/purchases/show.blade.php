<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Transaksi Pembelian #{{ $purchase->purchase_code }}
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
                            @if (!$purchase->trashed())
                                <form method="POST" action="{{ route('purchases.cancel', $purchase->id) }}" onsubmit="return confirm('Yakin ingin membatalkan transaksi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500">Batalkan</button>
                                </form>
                            @endif
                            @role('Admin')
                                <form method="POST" action="{{ route('purchases.destroy', $purchase->id) }}" onsubmit="return confirm('PERINGATAN: Aksi ini akan menghapus data permanen. Yakin?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">Hapus Permanen</button>
                                </form>
                            @endrole
                        </div>
                    </div>

                    {{-- [MODIFIKASI V1.9.0] Informasi Detail Transaksi dengan Status Bayar --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Kode Pembelian</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $purchase->purchase_code }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status Transaksi</h3>
                            <p class="mt-1 text-lg font-semibold">
                                @if ($purchase->trashed())
                                    <span class="px-3 py-1 text-sm font-semibold leading-tight text-red-700 bg-red-100 rounded-full">Dibatalkan</span>
                                @else
                                    <span class="px-3 py-1 text-sm font-semibold leading-tight text-blue-700 bg-blue-100 rounded-full">Selesai</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Tanggal & Waktu</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($purchase->purchase_date)->isoFormat('D MMM YYYY, HH:mm') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Dicatat Oleh</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $purchase->user->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6 border-b pb-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Supplier</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $purchase->supplier->name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status Pembayaran</h3>
                            <p class="mt-1 text-lg font-semibold">
                                @if ($purchase->payment_status == 'Lunas')
                                    <span class="px-3 py-1 text-sm font-semibold leading-tight text-green-700 bg-green-100 rounded-full">Lunas</span>
                                @elseif ($purchase->payment_status == 'Belum Lunas')
                                    <span class="px-3 py-1 text-sm font-semibold leading-tight text-yellow-700 bg-yellow-100 rounded-full">Belum Lunas</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Metode Pembayaran</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $purchase->payment_method }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">No. Referensi</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $purchase->reference_number ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Catatan</h3>
                            <p class="mt-1 text-gray-700">{{ $purchase->notes ?? 'Tidak ada catatan.' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Gambar Faktur</h3>
                            @if($purchase->invoice_image_path)
                                <a href="{{ asset('storage/' . $purchase->invoice_image_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $purchase->invoice_image_path) }}" alt="Gambar Faktur" class="mt-2 w-full max-w-xs h-auto object-cover rounded-md border">
                                </a>
                            @else
                                <p class="mt-1 text-gray-700">Tidak ada gambar faktur.</p>
                            @endif
                        </div>
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
                                    <td colspan="3" class="px-6 py-3 text-right">Total Pembelian</td>
                                    <td class="px-6 py-3 text-right">{{ 'Rp ' . number_format($purchase->total_amount, 0, ',', '.') }}</td>
                                </tr>
                                @if($purchase->payment_status == 'Belum Lunas')
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right">Uang Muka (DP)</td>
                                    <td class="px-6 py-3 text-right">{{ 'Rp ' . number_format($purchase->down_payment, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right">Sisa Tagihan</td>
                                    <td class="px-6 py-3 text-right">{{ 'Rp ' . number_format($purchase->total_amount - $purchase->total_paid, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>