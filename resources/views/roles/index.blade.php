<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Peran') }}
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

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-lg">Daftar Peran</h3>
                        <a href="{{ route('roles.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Tambah Peran
                        </a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                            <thead class="text-left">
                                <tr>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Nama Peran</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Aksi</th>
                                </tr>
                            </thead>
                    
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($roles as $role)
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">{{ $role->name }}</td>
                                        <td class="whitespace-nowrap px-4 py-2 flex space-x-2">
                                            <a href="{{ route('roles.edit', $role) }}" class="inline-block rounded bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700">
                                                Edit
                                            </a>
                                            <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus peran ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-block rounded bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="whitespace-nowrap px-4 py-4 text-center text-gray-500">
                                            Tidak ada peran yang ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- [BARU] Tambahkan blok ini untuk link paginasi --}}
                    <div class="mt-4">
                        {{ $roles->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>