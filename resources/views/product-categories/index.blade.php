<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Kategori Produk') }}
        </h2>
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

                    {{-- [BARU] Tambahkan blok ini untuk menampilkan pesan Eror --}}
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-lg">Daftar Kategori</h3>
                        {{-- Tombol ini hanya muncul untuk Admin dan Manager --}}
                        @hasanyrole('Admin|Manager')
                        <a href="{{ route('product-categories.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Tambah Kategori</a>
                        @endhasanyrole
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                            <thead class="text-left">
                                <tr>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Nama Kategori</th>
                                    {{-- Kolom Aksi hanya muncul untuk Admin dan Manager --}}
                                    @hasanyrole('Admin|Manager')
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Aksi</th>
                                    @endhasanyrole
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($categories as $category)
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">{{ $category->name }}</td>
                                        {{-- Tombol Edit & Hapus hanya muncul untuk Admin dan Manager --}}
                                        @hasanyrole('Admin|Manager')
                                        <td class="whitespace-nowrap px-4 py-2 flex space-x-2">
                                            <a href="{{ route('product-categories.edit', $category) }}" class="inline-block rounded bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700">Edit</a>
                                            <form action="{{ route('product-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Yakin hapus?');">
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
                                        <td colspan="{{ auth()->user()->hasAnyRole(['Admin', 'Manager']) ? '2' : '1' }}" class="whitespace-nowrap px-4 py-4 text-center text-gray-500">Tidak ada kategori.</td>
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