<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Transaksi Penjualan Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" 
                     x-data="saleForm" 
                     x-init="init()"
                     {{-- [PERBAIKAN] Menambahkan listener untuk event hapus --}}
                     @remove-item.window="items.splice($event.detail.index, 1); calculateTotal()">

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" role="alert">
                            <strong class="font-bold">Terjadi Kesalahan!</strong>
                            <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('sales.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="customer_id" :value="__('Pelanggan')" />
                                <select id="customer_id" name="customer_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ (old('customer_id') == $customer->id) || ($customer->name == 'Pelanggan Umum' && !old('customer_id')) ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="sale_date" :value="__('Tanggal Penjualan')" />
                                <x-text-input id="sale_date" class="block mt-1 w-full" type="datetime-local" name="sale_date" :value="old('sale_date', now()->format('Y-m-d\TH:i'))" required />
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Detail Produk</h3>
                                <button type="button" @click="toggleGallery()" class="text-sm font-medium text-indigo-600 hover:underline">
                                    Pilih dari Galeri
                                </button>
                            </div>
                            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 w-2/5">Produk</th>
                                            <th class="px-6 py-3">Jumlah</th>
                                            <th class="px-6 py-3">Harga Jual</th>
                                            <th class="px-6 py-3">Subtotal</th>
                                            <th class="px-6 py-3"><span class="sr-only">Aksi</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in items" :key="item.id">
                                            <tr x-data="saleItem(item, index)" x-init="init()" class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4 align-top">
                                                    <input type="hidden" x-bind:name="'items[' + index + '][product_id]'" x-model="item.product_id">
                                                    <select x-ref="select"></select>
                                                </td>
                                                <td class="px-6 py-4 align-top">
                                                    <x-text-input type="number" x-bind:name="'items[' + index + '][quantity]'" x-model.number="item.quantity" @input="calculateSubtotal()" class="block w-full" min="1" required />
                                                </td>
                                                <td class="px-6 py-4 align-top">
                                                    <x-text-input type="number" x-bind:name="'items[' + index + '][sale_price]'" x-model.number="item.sale_price" @input="calculateSubtotal()" class="block w-full" min="0" required />
                                                </td>
                                                <td class="px-6 py-4 align-top" x-text="formatCurrency(item.subtotal)"></td>
                                                <td class="px-6 py-4 align-top">
                                                    <button type="button" @click="$dispatch('remove-item', { index: index })" class="font-medium text-red-600 hover:underline">Hapus</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr><td colspan="5" class="px-6 py-4"><button type="button" @click="addItem()" class="text-sm font-medium text-blue-600 hover:underline">+ Tambah Produk</button></td></tr>
                                        <tr class="font-bold text-gray-900 bg-gray-50">
                                            <td colspan="3" class="px-6 py-3 text-right">Total</td>
                                            <td colspan="2" class="px-6 py-3">
                                                <span x-text="formatCurrency(total_amount)"></span>
                                                <input type="hidden" name="total_amount" x-model="total_amount">
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="mt-6">
                                <x-input-label for="notes" :value="__('Catatan')" />
                                <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md">Batal</a>
                            <x-primary-button class="ms-4">{{ __('Simpan Transaksi') }}</x-primary-button>
                        </div>

                        {{-- Modal Window untuk Galeri Produk --}}
                        <div x-show="gallery.isOpen" @click.self="toggleGallery()" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" style="display: none;">
                            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
                                <div class="p-4 border-b flex justify-between items-center">
                                    <h3 class="text-lg font-semibold">Pilih Produk dari Galeri</h3>
                                    <button type="button" @click="toggleGallery()" class="text-gray-500 hover:text-gray-800">&times;</button>
                                </div>
                                <div class="p-4 overflow-y-auto">
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                        <template x-for="product in gallery.products" :key="product.id">
                                            <div @click="selectFromGallery(product)" class="border rounded-lg overflow-hidden cursor-pointer hover:border-indigo-500 hover:shadow">
                                                <img :src="'/storage/' + product.image_path" :alt="product.name" class="w-full h-24 object-cover">
                                                <div class="p-2">
                                                    <p class="text-xs font-semibold truncate" x-text="product.name"></p>
                                                    <p class="text-xs text-gray-500" x-text="'Stok: ' + product.stock"></p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <div x-show="gallery.isLoading" class="text-center p-4">Memuat...</div>
                                    <div x-show="gallery.pagination.next_page_url && !gallery.isLoading" class="text-center p-4">
                                        <button type="button" @click="fetchGalleryProducts(gallery.pagination.next_page_url)" class="text-sm font-medium text-indigo-600 hover:underline">Muat Lebih Banyak</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Mengirim data 'old' dari PHP ke JavaScript
        window.oldItems = @json(old('items'));
        window.oldTotalAmount = @json(old('total_amount'));
    </script>
    @endpush
</x-app-layout>