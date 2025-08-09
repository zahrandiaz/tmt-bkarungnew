<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Produk</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- [BARU] Tambahkan enctype untuk upload file --}}
                    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" value="Nama Produk" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $product->name)" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                             <div>
                                <x-input-label for="product_category_id" value="Kategori Produk" />
                                <select name="product_category_id" id="product_category_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('product_category_id', $product->product_category_id) == $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="product_type_id" value="Jenis Produk" />
                                <select name="product_type_id" id="product_type_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" @selected(old('product_type_id', $product->product_type_id) == $type->id)>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="selling_price" value="Harga Jual" />
                                <x-text-input id="selling_price" class="block mt-1 w-full" type="number" name="selling_price" :value="old('selling_price', $product->selling_price)" required />
                            </div>
                            <div>
                                <x-input-label for="purchase_price" value="Harga Beli" />
                                <x-text-input id="purchase_price" class="block mt-1 w-full" type="number" name="purchase_price" :value="old('purchase_price', $product->purchase_price)" required />
                            </div>
                            <div>
                                <x-input-label for="stock" value="Stok" />
                                <x-text-input id="stock" class="block mt-1 w-full" type="number" name="stock" :value="old('stock', $product->stock)" required />
                            </div>
                            <div>
                                <x-input-label for="min_stock_level" value="Stok Minimum" />
                                <x-text-input id="min_stock_level" class="block mt-1 w-full" type="number" name="min_stock_level" :value="old('min_stock_level', $product->min_stock_level)" required />
                            </div>

                            {{-- [BARU] Input untuk gambar produk dengan preview --}}
                            <div>
                                <x-input-label for="image" value="Ganti Gambar Produk (Opsional)" />
                                <input type="file" name="image" id="image" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none mt-1">
                                <p class="mt-1 text-sm text-gray-500">Kosongkan jika tidak ingin mengganti gambar.</p>
                                <x-input-error :messages="$errors->get('image')" class="mt-2" />

                                @if ($product->image_path)
                                    <div class="mt-4">
                                        <p class="text-sm font-medium text-gray-700">Gambar Saat Ini:</p>
                                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="mt-2 w-32 h-32 object-cover rounded-md">
                                    </div>
                                @endif
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="description" value="Deskripsi" />
                                <textarea name="description" id="description" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $product->description) }}</textarea>
                            </div>
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('products.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <x-primary-button>Perbarui</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>