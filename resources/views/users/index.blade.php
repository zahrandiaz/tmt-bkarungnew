<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Notifikasi Sukses --}}
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Notifikasi Eror --}}
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-lg">Daftar Pengguna</h3>
                        @can('user-create')
                            <a href="{{ route('users.create') }}" class="inline-block rounded bg-green-600 px-4 py-2 text-xs font-medium text-white hover:bg-green-700">
                                Tambah Pengguna Baru
                            </a>
                        @endcan
                    </div>

                    <div class="mb-4">
                            <form action="{{ route('users.index') }}" method="GET">
                                <div class="flex items-center">
                                    <input type="text" name="search" placeholder="Cari nama atau email..." class="w-full md:w-1/3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ $search ?? '' }}">
                                    <button type="submit" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Cari</button>
                                </div>
                            </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                            <thead class="text-left">
                                <tr>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Nama</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Email</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Peran</th>
                                    {{-- [PERBAIKAN] Ganti @can('user-edit') menjadi @canany --}}
                                    @canany(['user-edit', 'user-delete'])
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Aksi</th>
                                    @endcanany
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                @forelse ($users as $user)
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">{{ $user->name }}</td>
                                        <td class="whitespace-nowrap px-4 py-2 text-gray-700">{{ $user->email }}</td>
                                        <td class="whitespace-nowrap px-4 py-2 text-gray-700">
                                            <span class="inline-flex items-center justify-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-emerald-700">
                                                {{ $user->getRoleNames()->first() ?? 'Belum ada peran' }}
                                            </span>
                                        </td>
                                        {{-- [PERBAIKAN] Ganti @can('user-edit') menjadi @canany --}}
                                        @canany(['user-edit', 'user-delete'])
                                        <td class="whitespace-nowrap px-4 py-2">
                                            {{-- [PERBAIKAN] Bungkus tombol dalam div flex --}}
                                            <div class="flex gap-2">
                                                @can('user-edit')
                                                <a href="{{ route('users.edit', $user) }}" class="inline-block rounded bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700">
                                                    Edit
                                                </a>
                                                @endcan

                                                {{-- [BARU] Tambahkan form dan tombol Hapus --}}
                                                @can('user-delete')
                                                <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-block rounded bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700">
                                                        Hapus
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        {{-- [PERBAIKAN] Sesuaikan colspan menjadi 4 --}}
                                        <td colspan="4" class="whitespace-nowrap px-4 py-4 text-center text-gray-500">
                                            Tidak ada pengguna yang ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>