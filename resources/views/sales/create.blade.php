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

                    {{-- [BARU] Menampilkan eror umum di bagian atas --}}
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" role="alert">
                            <strong class="font-bold">Terjadi Kesalahan!</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('sales.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="customer_id" :value="__('Pelanggan')" />
                                <select id="customer_id" name="customer_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled {{ old('customer_id') ? '' : 'selected' }}>Pilih Pelanggan</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="sale_date" :value="__('Tanggal Penjualan')" />
                                <x-text-input id="sale_date" class="block mt-1 w-full" type="date" name="sale_date" :value="old('sale_date', date('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('sale_date')" class="mt-2" />
                            </div>
                        </div>

                        <div x-data="saleForm()" x-init='initProducts(@json($products))'>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Produk</h3>
                            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3">Produk</th>
                                            <th class="px-6 py-3">Jumlah</th>
                                            <th class="px-6 py-3">Harga Jual Satuan</th>
                                            <th class="px-6 py-3">Subtotal</th>
                                            <th class="px-6 py-3"><span class="sr-only">Aksi</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4 align-top">
                                                    <select x-bind:name="'items[' + index + '][product_id]'" x-model="item.product_id" @change="updatePrice(index)" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                                        <option value="" disabled>Pilih Produk</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    {{-- [BARU] Penampil eror untuk setiap baris item --}}
                                                     <div x-show="$errors.has('items.' + index + '.product_id')">
                                                        <p class="text-sm text-red-600" x-text="$errors.first('items.' + index + '.product_id')"></p>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 align-top">
                                                    <x-text-input type="number" x-bind:name="'items[' + index + '][quantity]'" x-model.number="item.quantity" @input="calculateSubtotal(index)" class="block w-full" min="1" required />
                                                    {{-- [BARU] Penampil eror untuk setiap baris item, termasuk eror stok --}}
                                                     <div x-show="$errors.has('items.' + index + '.quantity')">
                                                        <p class="text-sm text-red-600" x-text="$errors.first('items.' + index + '.quantity')"></p>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 align-top">
                                                    <x-text-input type="number" x-bind:name="'items[' + index + '][sale_price]'" x-model.number="item.sale_price" @input="calculateSubtotal(index)" class="block w-full" min="0" required />
                                                </td>
                                                <td class="px-6 py-4 align-top"><span x-text="formatCurrency(item.subtotal)"></span></td>
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
                                <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">Batal</a>
                            <x-primary-button class="ms-4">{{ __('Simpan Transaksi') }}</x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        function saleForm() {
            // Mengambil data lama jika ada eror validasi, jika tidak, mulai dengan satu item kosong
            const oldItems = @json(old('items')) || [{ product_id: '', quantity: 1, sale_price: 0, subtotal: 0 }];

            return {
                items: oldItems,
                total_amount: @json(old('total_amount', 0)),
                allProducts: [],
                errors: @json($errors->getMessages()), // [BARU] Ambil semua eror
                initProducts(products) { this.allProducts = products; this.calculateTotal(); },
                addItem() { this.items.push({ product_id: '', quantity: 1, sale_price: 0, subtotal: 0 }); },
                removeItem(index) { this.items.splice(index, 1); this.calculateTotal(); },
                calculateSubtotal(index) {
                    const item = this.items[index];
                    item.subtotal = (item.quantity || 0) * (item.sale_price || 0);
                    this.calculateTotal();
                },
                calculateTotal() { this.total_amount = this.items.reduce((total, item) => total + (item.subtotal || 0), 0); },
                formatCurrency(value) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value); },
                updatePrice(index) {
                    const selectedProductId = this.items[index].product_id;
                    if (!selectedProductId) return;
                    const product = this.allProducts.find(p => p.id == selectedProductId);
                    if (product) {
                        this.items[index].sale_price = product.selling_price;
                    }
                    this.calculateSubtotal(index);
                }
            }
        }
    </script>
</x-app-layout>