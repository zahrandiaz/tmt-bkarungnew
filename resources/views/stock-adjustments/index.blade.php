<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Penyesuaian Stok') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg" x-data="stockAdjustmentForm()">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900">Buat Penyesuaian Baru</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Gunakan form ini untuk mencatat penambahan atau pengurangan stok di luar transaksi jual-beli.
                    </p>

                    @if(session('success'))
                        <div class="mt-4 font-medium text-sm text-green-600 bg-green-100 border border-green-400 p-4 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                         <div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" role="alert">
                            <strong class="font-bold">Terjadi Kesalahan!</strong>
                            <p>{{ session('error') }}</p>
                         </div>
                    @endif
                     @if ($errors->any())
                        <div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" role="alert">
                            <strong class="font-bold">Terjadi Kesalahan Validasi!</strong>
                            <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                        </div>
                    @endif

                    <form method="post" action="{{ route('stock-adjustments.store') }}" class="mt-6 space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="product_id" :value="__('Pilih Produk')" />
                            <select x-ref="selectProduct" name="product_id" placeholder="Cari produk berdasarkan nama atau SKU..." autocomplete="off">
                                @if(old('product_id'))
                                    <option value="{{ old('product_id') }}" selected>{{ old('product_name_display') }}</option>
                                @endif
                            </select>
                            <input type="hidden" name="product_name_display" x-model="selectedProductName">
                        </div>

                        <div>
                            <x-input-label for="type" :value="__('Tipe Penyesuaian')" />
                            <select name="type" id="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="increment" @selected(old('type') == 'increment')>Penambahan</option>
                                <option value="decrement" @selected(old('type') == 'decrement')>Pengurangan</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="quantity" :value="__('Jumlah')" />
                            <x-text-input id="quantity" name="quantity" type="number" class="mt-1 block w-full" :value="old('quantity')" min="1" />
                        </div>

                        <div>
                            <x-input-label for="reason" :value="__('Alasan Penyesuaian')" />
                            <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full" :value="old('reason')" placeholder="Contoh: Hasil Stock Opname, Barang Rusak, dll." />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Riwayat Penyesuaian Stok</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="px-4 py-2 text-left">Tanggal</th>
                                    <th class="px-4 py-2 text-left">Nama Produk</th>
                                    <th class="px-4 py-2 text-center">Tipe</th>
                                    <th class="px-4 py-2 text-right">Jumlah</th>
                                    <th class="px-4 py-2 text-left">Alasan</th>
                                    <th class="px-4 py-2 text-left">Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($adjustments as $adjustment)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-2">{{ $adjustment->created_at->format('d M Y, H:i') }}</td>
                                        <td class="px-4 py-2">{{ $adjustment->product->name ?? 'Produk Dihapus' }}</td>
                                        <td class="px-4 py-2 text-center">
                                            @if($adjustment->type == 'increment')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Penambahan</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Pengurangan</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-right font-medium">{{ $adjustment->type == 'increment' ? '+' : '-' }}{{ $adjustment->quantity }}</td>
                                        <td class="px-4 py-2">{{ $adjustment->reason }}</td>
                                        <td class="px-4 py-2">{{ $adjustment->user->name ?? 'User Dihapus' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-4 py-4 text-center text-gray-500">Belum ada riwayat penyesuaian stok.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $adjustments->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        window.oldData = {
            product_name_display: @json(old('product_name_display'))
        };
    </script>
    @endpush
</x-app-layout>