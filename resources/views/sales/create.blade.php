<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Transaksi Penjualan Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" role="alert">
                            <strong class="font-bold">Terjadi Kesalahan!</strong>
                            <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('sales.store') }}" x-data="saleForm" x-init="init()">
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
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Produk</h3>
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
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4 align-top">
                                                    <select x-init="initTomSelect($el, index)" x-bind:name="'items[' + index + '][product_id]'" x-model="item.product_id" class="tom-select-element"></select>
                                                </td>
                                                <td class="px-6 py-4 align-top">
                                                    <x-text-input type="number" x-bind:name="'items[' + index + '][quantity]'" x-model.number="item.quantity" @input="calculateSubtotal(index)" class="block w-full" min="1" required />
                                                </td>
                                                <td class="px-6 py-4 align-top">
                                                    <x-text-input type="number" x-bind:name="'items[' + index + '][sale_price]'" x-model.number="item.sale_price" @input="calculateSubtotal(index)" class="block w-full" min="0" required />
                                                </td>
                                                <td class="px-6 py-4 align-top" x-text="formatCurrency(item.subtotal)"></td>
                                                <td class="px-6 py-4 align-top"><button type="button" @click="removeItem(index)" class="font-medium text-red-600 hover:underline">Hapus</button></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr><td colspan="5" class="px-6 py-4"><button type="button" @click="addItem()" class="text-sm font-medium text-blue-600 hover:underline">+ Tambah Produk</button></td></tr>
                                        <tr class="font-bold text-gray-900 bg-gray-50">
                                            <td colspan="3" class="px-6 py-3 text-right">Total Penjualan</td>
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
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">Batal</a>
                            <x-primary-button class="ms-4">{{ __('Simpan Transaksi') }}</x-primary-button>
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