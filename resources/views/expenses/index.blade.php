<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Biaya Operasional') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
                        <h3 class="text-lg font-medium text-gray-900 w-full sm:w-auto">Daftar Biaya</h3>
                        <div class="w-full sm:w-auto flex sm:justify-end items-center gap-4">
                            <form action="{{ route('expenses.index') }}" method="GET" class="w-full sm:w-auto sm:max-w-xs">
                                <div class="flex items-center">
                                    <input type="text" name="search" placeholder="Cari nama biaya..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ $search ?? '' }}">
                                    <button type="submit" class="ml-2 inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                        Cari
                                    </button>
                                </div>
                            </form>
                             <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 w-full sm:w-auto justify-center">
                                Tambah Biaya
                            </a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">No. Urut</th>
                                    <th scope="col" class="px-6 py-3">Tanggal</th>
                                    <th scope="col" class="px-6 py-3">Nama Biaya</th>
                                    <th scope="col" class="px-6 py-3">Kategori</th>
                                    <th scope="col" class="px-6 py-3 text-right">Jumlah</th>
                                    <th scope="col" class="px-6 py-3">
                                        <span class="sr-only">Aksi</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($expenses as $index => $expense)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">{{ $expenses->firstItem() + $index }}</td>
                                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $expense->name }}
                                        </td>
                                        <td class="px-6 py-4">{{ $expense->category->name }}</td>
                                        <td class="px-6 py-4 text-right">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('expenses.edit', $expense) }}" class="font-medium text-blue-600 hover:underline mr-3">Edit</a>
                                            <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus biaya ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-red-600 hover:underline">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                             @if ($search)
                                                Biaya dengan kata kunci "{{ $search }}" tidak ditemukan.
                                            @else
                                                Tidak ada data biaya operasional.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $expenses->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>