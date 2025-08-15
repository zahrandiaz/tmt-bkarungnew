<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Pembelian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Jumlah Transaksi</h3>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Pengeluaran</h3>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">Rp {{ number_format($totalExpenditure, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold mb-4">Filter Laporan</h3>
                    <div class="flex items-center space-x-2 mb-4">
                        <a href="{{ route('reports.purchases', ['period' => 'today']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'today' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Hari Ini</a>
                        <a href="{{ route('reports.purchases', ['period' => 'this_week']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'this_week' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Minggu Ini</a>
                        <a href="{{ route('reports.purchases', ['period' => 'this_month']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'this_month' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Bulan Ini</a>
                        <a href="{{ route('reports.purchases', ['period' => 'this_year']) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $period == 'this_year' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Tahun Ini</a>
                    </div>
                    <form action="{{ route('reports.purchases') }}" method="GET">
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
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Filter Manual</button>
                                <a href="{{ route('reports.purchases.export.csv', request()->query()) }}" class="ml-2 inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500">Ekspor CSV</a>
                                <a href="{{ route('reports.purchases.export.pdf', request()->query()) }}" target="_blank" class="ml-2 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">Unduh PDF</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-3"></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Pembelian</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Transaksi</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" x-data="{ 
                                openRowId: null, 
                                detailsCache: {},
                                loadingId: null,
                                toggleDetails(transactionId, type) {
                                    if (this.openRowId === transactionId) { this.openRowId = null; return; }
                                    this.openRowId = transactionId;
                                    if (this.detailsCache[transactionId]) { return; }
                                    this.loadingId = transactionId;
                                    fetch(`/api/reports/${type}-details/${transactionId}`)
                                        .then(response => response.json())
                                        .then(data => { this.detailsCache[transactionId] = data; })
                                        .catch(error => console.error('Error:', error))
                                        .finally(() => { this.loadingId = null; });
                                },
                                formatCurrency(value) {
                                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
                                }
                            }">
                                @forelse ($purchases as $purchase)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-2 py-4">
                                            <button @click="toggleDetails({{ $purchase->id }}, 'purchase')" class="text-blue-500 hover:text-blue-700">
                                                <svg x-show="openRowId !== {{ $purchase->id }}" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <svg x-show="openRowId === {{ $purchase->id }}" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration + $purchases->firstItem() - 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <a href="{{ route('purchases.show', $purchase->id) }}" class="text-blue-600 hover:underline" target="_blank">
                                                {{ $purchase->purchase_code }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($purchase->purchase_date)->isoFormat('D MMM YYYY, HH:mm') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $purchase->supplier->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            @if ($purchase->trashed())
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Dibatalkan</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr x-show="openRowId === {{ $purchase->id }}" x-transition>
                                        <td colspan="7" class="p-4 bg-gray-50">
                                            <div x-show="loadingId === {{ $purchase->id }}">Memuat detail...</div>
                                            <div x-show="detailsCache[{{ $purchase->id }}] && loadingId !== {{ $purchase->id }}">
                                                <h4 class="font-bold mb-2">Rincian Item:</h4>
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-100">
                                                        <tr>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Harga Beli</th>
                                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        <template x-for="detail in detailsCache[{{ $purchase->id }}]" :key="detail.id">
                                                            <tr>
                                                                <td class="px-4 py-2" x-text="detail.product.name"></td>
                                                                <td class="px-4 py-2 text-center" x-text="detail.quantity"></td>
                                                                <td class="px-4 py-2 text-right" x-text="formatCurrency(detail.purchase_price)"></td>
                                                                <td class="px-4 py-2 text-right" x-text="formatCurrency(detail.quantity * detail.purchase_price)"></td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Tidak ada data untuk ditampilkan pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $purchases->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>