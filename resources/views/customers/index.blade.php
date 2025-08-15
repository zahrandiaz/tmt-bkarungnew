<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Pelanggan') }}
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

                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
                        {{-- [MODIFIKASI V2.0.0] Ganti @hasanyrole menjadi @can --}}
                        @can('product-create')
                            <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 w-full sm:w-auto">
                                Tambah Pelanggan
                            </a>
                        @endcan
                        <form action="{{ route('customers.index') }}" method="GET" class="w-full sm:w-auto sm:max-w-xs ml-auto">
                            <div class="flex items-center">
                                <input type="text" name="search" placeholder="Cari nama/telepon..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ $search ?? '' }}">
                                <button type="submit" class="ml-2 inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    Cari
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama</th>
                                    <th scope="col" class="px-6 py-3">Telepon</th>
                                    <th scope="col" class="px-6 py-3">Alamat</th>
                                    {{-- [MODIFIKASI V2.0.0] Cek setidaknya salah satu permission edit/delete --}}
                                    @if(auth()->user()->can('product-edit') || auth()->user()->can('product-delete'))
                                    <th scope="col" class="px-6 py-3">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($customers as $customer)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $customer->name }}
                                    </th>
                                    <td class="px-6 py-4">
                                        {{ $customer->phone }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $customer->address }}
                                    </td>
                                    {{-- [MODIFIKASI V2.0.0] Ganti @hasanyrole menjadi @can per tombol --}}
                                    @if(auth()->user()->can('product-edit') || auth()->user()->can('product-delete'))
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            @can('product-edit')
                                            <a href="{{ route('customers.edit', $customer->id) }}" class="inline-block rounded bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700">Edit</a>
                                            @endcan
                                            @can('product-delete')
                                            <form method="POST" action="{{ route('customers.destroy', $customer->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-block rounded bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700">Hapus</button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                                @empty
                                <tr class="bg-white border-b">
                                    {{-- [MODIFIKASI V2.0.0] Sesuaikan colspan --}}
                                    <td colspan="{{ auth()->user()->canany(['product-edit', 'product-delete']) ? '4' : '3' }}" class="px-6 py-4 text-center">
                                        @if ($search ?? false)
                                            Pelanggan dengan kata kunci "{{ $search }}" tidak ditemukan.
                                        @else
                                            Tidak ada data.
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $customers->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>