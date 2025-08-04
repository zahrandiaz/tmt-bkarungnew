<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Produk Baru</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('products.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" value="Nama Produk" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                             <div>
                                <x-input-label for="product_category_id" value="Kategori Produk" />
                                <select name="product_category_id" id="product_category_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="product_type_id" value="Jenis Produk" />
                                <select name="product_type_id" id="product_type_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="selling_price" value="Harga Jual" />
                                <x-text-input id="selling_price" class="block mt-1 w-full" type="number" name="selling_price" :value="old('selling_price')" required />
                            </div>
                            <div>
                                <x-input-label for="purchase_price" value="Harga Beli" />
                                <x-text-input id="purchase_price" class="block mt-1 w-full" type="number" name="purchase_price" :value="old('purchase_price')" required />
                            </div>
                            <div>
                                <x-input-label for="stock" value="Stok Awal" />
                                <x-text-input id="stock" class="block mt-1 w-full" type="number" name="stock" :value="old('stock')" required />
                            </div>
                            <div>
                                <x-input-label for="min_stock_level" value="Stok Minimum" />
                                <x-text-input id="min_stock_level" class="block mt-1 w-full" type="number" name="min_stock_level" :value="old('min_stock_level')" required />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="description" value="Deskripsi" />
                                <textarea name="description" id="description" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                            </div>
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('products.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <x-primary-button>Simpan</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>