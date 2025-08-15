<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Penyesuaian Harga Jual Massal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if(session('success'))
                        <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 border border-green-400 p-4 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 border border-red-400 p-4 rounded-md">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <div class="mb-4">
                        <form action="{{ route('price-adjustments.index') }}" method="GET">
                            <div class="flex items-center space-x-4">
                                <div>
                                    <x-input-label for="category_id" :value="__('Filter by Category')" />
                                    <select name="category_id" id="category_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Semua Kategori</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="pt-6">
                                    <x-primary-button>
                                        {{ __('Filter') }}
                                    </x-primary-button>
                                    <a href="{{ route('price-adjustments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <form action="{{ route('price-adjustments.store') }}" method="POST">
                        @csrf

                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="w-1/12 px-4 py-2 text-left">SKU</th>
                                        <th class="w-4/12 px-4 py-2 text-left">Nama Produk</th>
                                        <th class="w-2/12 px-4 py-2 text-left">Kategori</th>
                                        <th class="w-2/12 px-4 py-2 text-right">Harga Jual Saat Ini</th>
                                        <th class="w-3/12 px-4 py-2 text-center">Harga Jual Baru</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $product)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="px-4 py-2 align-top">{{ $product->sku }}</td>
                                            <td class="px-4 py-2 align-top">{{ $product->name }}</td>
                                            <td class="px-4 py-2 align-top">{{ $product->category->name ?? '-' }}</td>
                                            <td class="px-4 py-2 text-right align-top">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2">
                                                <input type="hidden" name="products[{{ $product->id }}][id]" value="{{ $product->id }}">
                                                <x-text-input 
                                                    type="number" 
                                                    name="products[{{ $product->id }}][selling_price]" 
                                                    class="block w-full text-right" 
                                                    value="{{ old('products.'.$product->id.'.selling_price', $product->selling_price) }}"
                                                    />
                                                <x-input-error :messages="$errors->get('products.' . $product->id . '.selling_price')" class="mt-2 text-right" />
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                                                Tidak ada produk yang ditemukan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $products->links() }}
                        </div>

                        {{-- [MODIFIKASI V2.0.0] Ganti @if menjadi @can --}}
                        @can('adjustment-price')
                            @if($products->isNotEmpty())
                            <div class="flex items-center justify-end mt-6">
                                <x-primary-button>
                                    {{ __('Simpan Perubahan') }}
                                </x-primary-button>
                            </div>
                            @endif
                        @endcan
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>