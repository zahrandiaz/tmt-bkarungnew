<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Jenis Produk') }}
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

                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-lg">Daftar Jenis</h3>
                        {{-- [MODIFIKASI V2.0.0] Ganti @hasanyrole menjadi @can --}}
                        @can('product-create')
                        <a href="{{ route('product-types.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Tambah Jenis</a>
                        @endcan
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                            <thead class="text-left">
                                <tr>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Nama Jenis</th>
                                    {{-- [MODIFIKASI V2.0.0] Cek setidaknya salah satu permission edit/delete --}}
                                    @if(auth()->user()->can('product-edit') || auth()->user()->can('product-delete'))
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($types as $type)
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">{{ $type->name }}</td>
                                        {{-- [MODIFIKASI V2.0.0] Ganti @hasanyrole menjadi @can per tombol --}}
                                        @if(auth()->user()->can('product-edit') || auth()->user()->can('product-delete'))
                                        <td class="whitespace-nowrap px-4 py-2 flex space-x-2">
                                            @can('product-edit')
                                            <a href="{{ route('product-types.edit', $type) }}" class="inline-block rounded bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700">Edit</a>
                                            @endcan
                                            @can('product-delete')
                                            <form action="{{ route('product-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Yakin hapus?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-block rounded bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700">Hapus</button>
                                            </form>
                                            @endcan
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        {{-- [MODIFIKASI V2.0.0] Sesuaikan colspan --}}
                                        <td colspan="{{ auth()->user()->canany(['product-edit', 'product-delete']) ? '2' : '1' }}" class="whitespace-nowrap px-4 py-4 text-center text-gray-500">Tidak ada jenis produk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $types->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>