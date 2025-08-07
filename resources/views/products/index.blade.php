<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Produk</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-lg">Daftar Produk</h3>
                        {{-- Tombol ini hanya muncul untuk Admin dan Manager --}}
                        @hasanyrole('Admin|Manager')
                        <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Tambah Produk</a>
                        @endhasanyrole
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                            <thead class="text-left">
                                <tr>
                                    <th class="p-2">SKU</th>
                                    <th class="p-2">Nama</th>
                                    <th class="p-2">Kategori</th>
                                    <th class="p-2">Jenis</th>
                                    <th class="p-2">Harga Jual</th>
                                    <th class="p-2">Stok</th>
                                    {{-- Kolom Aksi hanya muncul untuk Admin dan Manager --}}
                                    @hasanyrole('Admin|Manager')
                                    <th class="p-2">Aksi</th>
                                    @endhasanyrole
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($products as $product)
                                    <tr>
                                        <td class="p-2">{{ $product->sku }}</td>
                                        <td class="p-2">{{ $product->name }}</td>
                                        <td class="p-2">{{ $product->category->name }}</td>
                                        <td class="p-2">{{ $product->type->name }}</td>
                                        <td class="p-2">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                                        <td class="p-2">{{ $product->stock }}</td>
                                        {{-- Tombol Edit & Hapus hanya muncul untuk Admin dan Manager --}}
                                        @hasanyrole('Admin|Manager')
                                        <td class="p-2 flex space-x-2">
                                            <a href="{{ route('products.edit', $product) }}" class="inline-block rounded bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700">Edit</a>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-block rounded bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700">Hapus</button>
                                            </form>
                                        </td>
                                        @endhasanyrole
                                    </tr>
                                @empty
                                    <tr>
                                        {{-- Sesuaikan colspan berdasarkan hak akses --}}
                                        <td colspan="{{ auth()->user()->hasAnyRole(['Admin', 'Manager']) ? '7' : '6' }}" class="p-4 text-center">Tidak ada produk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>