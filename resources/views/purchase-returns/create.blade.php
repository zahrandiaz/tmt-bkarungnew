<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Retur Pembelian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900"
                     x-data="{
                        formData: {
                            return_date: new Date().toISOString().slice(0, 16),
                            purchase_id: '',
                            notes: '',
                            items: []
                        },
                        items: [],
                        totalAmount: 0,
                        
                        init() {
                            this.initTomSelect();
                        },

                        initTomSelect() {
                            new TomSelect(this.$refs.purchaseSearch, {
                                maxItems: 1,
                                valueField: 'id',
                                labelField: 'text',
                                searchField: 'text',
                                load: (query, callback) => {
                                    if (!query.length) return callback();
                                    fetch(`{{ route('api.purchases.search') }}?q=${encodeURIComponent(query)}`)
                                        .then(response => response.json())
                                        .then(json => {
                                            callback(json);
                                        }).catch(()=>{
                                            callback();
                                        });
                                },
                                onChange: (value) => {
                                    this.formData.purchase_id = value;
                                    this.fetchPurchaseDetails();
                                }
                            });
                        },

                        fetchPurchaseDetails() {
                            if (!this.formData.purchase_id) {
                                this.items = [];
                                this.calculateTotal();
                                return;
                            }
                            fetch(`/api/purchases/${this.formData.purchase_id}/details`)
                                .then(response => response.json())
                                .then(data => {
                                    this.items = data.details.filter(detail => detail.returnable_quantity > 0)
                                    .map(detail => ({
                                        product_id: detail.product_id,
                                        product_name: detail.product.name,
                                        unit_price: parseFloat(detail.purchase_price) || 0,
                                        purchased_quantity: parseInt(detail.quantity) || 0,
                                        returnable_quantity: parseInt(detail.returnable_quantity) || 0,
                                        return_quantity: 0,
                                        subtotal: 0
                                    }));
                                    this.calculateTotal();
                                });
                        },

                        updateSubtotal(index) {
                            let item = this.items[index];
                            if (item.return_quantity > item.returnable_quantity) {
                                item.return_quantity = item.returnable_quantity;
                            }
                            if (item.return_quantity < 0 || item.return_quantity === '') {
                                item.return_quantity = 0;
                            }
                            item.subtotal = item.return_quantity * item.unit_price;
                            this.calculateTotal();
                        },

                        calculateTotal() {
                            this.totalAmount = this.items.reduce((acc, item) => acc + item.subtotal, 0);
                        },
                        
                        formatCurrency(amount) {
                            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
                        },

                        submitForm() {
                            this.formData.items = this.items.filter(item => item.return_quantity > 0);
                            
                            if (this.formData.items.length === 0) {
                                alert('Tidak ada item yang dipilih untuk diretur. Harap isi kuantitas retur minimal pada satu item.');
                                return; 
                            }
                            
                            fetch('{{ route('purchase-returns.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                                },
                                body: JSON.stringify(this.formData)
                            })
                            .then(response => {
                                if (response.ok) {
                                    window.location.href = '{{ route('purchase-returns.index') }}';
                                } else {
                                     response.json().then(data => {
                                        console.error('Server merespons dengan error:', data);
                                        alert('Terjadi kesalahan. Pastikan semua data terisi. Cek console (F12) untuk detail.');
                                    });
                                }
                            });
                        }
                    }"
                     x-init="init()">
                    <form @submit.prevent="submitForm">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="return_date" :value="__('Tanggal Retur')" />
                                <x-text-input id="return_date" class="block mt-1 w-full" type="datetime-local" name="return_date" x-model="formData.return_date" required />
                            </div>

                            <div>
                                <x-input-label for="purchase_id" :value="__('Cari Kode Pembelian')" />
                                <input x-ref="purchaseSearch" id="purchase_id" name="purchase_id" type="text" placeholder="Ketik Kode Pembelian atau Nama Supplier..." class="block mt-1 w-full">
                                <x-input-error :messages="$errors->get('purchase_id')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Catatan')" />
                                <textarea id="notes" name="notes" x-model="formData.notes" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                            </div>
                        </div>

                        <div class="mt-8 border-t pt-6" x-show="items.length > 0">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Item untuk Diretur</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                                    <thead>
                                        <tr>
                                            <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900 text-left">Produk</th>
                                            <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900 text-left">Harga Satuan</th>
                                            <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900 text-left">Qty Dibeli</th>
                                            <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900 text-left">Qty Bisa Diretur</th>
                                            <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900 text-left" style="width: 120px;">Qty Diretur</th>
                                            <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900 text-left">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td class="whitespace-nowrap px-4 py-2" x-text="item.product_name"></td>
                                                <td class="whitespace-nowrap px-4 py-2" x-text="formatCurrency(item.unit_price)"></td>
                                                <td class="whitespace-nowrap px-4 py-2" x-text="item.purchased_quantity"></td>
                                                <td class="whitespace-nowrap px-4 py-2 text-green-600 font-medium" x-text="item.returnable_quantity"></td>
                                                <td class="whitespace-nowrap px-4 py-2">
                                                    <x-text-input type="number" x-model.number="item.return_quantity" @input="updateSubtotal(index)" class="w-full text-sm" min="0" ::max="item.returnable_quantity" />
                                                </td>
                                                <td class="whitespace-nowrap px-4 py-2" x-text="formatCurrency(item.subtotal)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-right px-4 py-2 font-bold">Total Nilai Retur:</td>
                                            <td class="px-4 py-2 font-bold" x-text="formatCurrency(totalAmount)"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mt-8 border-t pt-6 text-center text-gray-500" x-show="formData.purchase_id && items.length === 0">
                            Semua item dari transaksi ini sudah diretur sepenuhnya.
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('purchase-returns.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <x-primary-button>
                                {{ __('Simpan Retur') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>