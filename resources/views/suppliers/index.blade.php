<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Supplier') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Tombol ini hanya muncul untuk Admin dan Manager --}}
                    @hasanyrole('Admin|Manager')
                    <div class="mb-4">
                        <a href="{{ route('suppliers.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Tambah Supplier
                        </a>
                    </div>
                    @endhasanyrole

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama</th>
                                    <th scope="col" class="px-6 py-3">Telepon</th>
                                    <th scope="col" class="px-6 py-3">Alamat</th>
                                    {{-- Kolom Aksi hanya muncul untuk Admin dan Manager --}}
                                    @hasanyrole('Admin|Manager')
                                    <th scope="col" class="px-6 py-3">Aksi</th>
                                    @endhasanyrole
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($suppliers as $supplier)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $supplier->name }}
                                    </th>
                                    <td class="px-6 py-4">
                                        {{ $supplier->phone }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $supplier->address }}
                                    </td>
                                    {{-- Tombol Edit & Hapus hanya muncul untuk Admin dan Manager --}}
                                    @hasanyrole('Admin|Manager')
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('suppliers.edit', $supplier->id) }}" class="font-medium text-blue-600 hover:underline">Edit</a>
                                            
                                            <form method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus supplier ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-red-600 hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                    @endhasanyrole
                                </tr>
                                @empty
                                <tr class="bg-white border-b">
                                    {{-- Sesuaikan colspan berdasarkan hak akses --}}
                                    <td colspan="{{ auth()->user()->hasAnyRole(['Admin', 'Manager']) ? '4' : '3' }}" class="px-6 py-4 text-center">
                                        Tidak ada data.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $suppliers->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>