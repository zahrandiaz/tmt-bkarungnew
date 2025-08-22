<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Log Aktivitas Pengguna') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="mb-6">
                        <form action="{{ route('activity-log.index') }}" method="GET">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                <!-- Pencarian -->
                                <div class="md:col-span-2">
                                    <label for="search" class="block text-sm font-medium text-gray-700">Cari Deskripsi / Pengguna</label>
                                    <input type="text" name="search" id="search" placeholder="Cari..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ $search ?? '' }}">
                                </div>
                                <!-- Filter Periode -->
                                <div>
                                    <label for="period" class="block text-sm font-medium text-gray-700">Periode</label>
                                    <select name="period" id="period" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="all_time" @selected($period == 'all_time')>Seluruh Waktu</option>
                                        <option value="today" @selected($period == 'today')>Hari Ini</option>
                                        <option value="this_week" @selected($period == 'this_week')>Minggu Ini</option>
                                        <option value="this_month" @selected($period == 'this_month')>Bulan Ini</option>
                                        <option value="this_year" @selected($period == 'this_year')>Tahun Ini</option>
                                    </select>
                                </div>
                                <!-- Tombol Filter -->
                                <div class="flex space-x-2">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Filter</button>
                                    <a href="{{ route('activity-log.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">Reset</a>
                                </div>
                            </div>
                            <!-- Tombol Ekspor -->
                            <div class="flex justify-end space-x-2 mt-4">
                                <button type="submit" name="export" value="csv" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">Unduh CSV</button>
                                <button type="submit" name="export" value="pdf" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">Unduh PDF</button>
                            </div>
                        </form>
                    </div>

                    <div class="flex justify-between items-center mb-4">
                        <div class="text-sm text-gray-600">
                            Menampilkan {{ $activities->firstItem() }}-{{ $activities->lastItem() }} dari {{ $activities->total() }} hasil.
                        </div>
                        @can('log-delete')
                            <form action="{{ route('activity-log.reset') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua riwayat log aktivitas? Aksi ini tidak dapat dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                    Reset Riwayat
                                </button>
                            </form>
                        @endcan
                    </div>

                    <!-- Tabel Log Aktivitas -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktivitas</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($activities as $activity)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $activity->created_at->diffForHumans() }}
                                            <div class="text-xs text-gray-400">{{ $activity->created_at->format('d M Y, H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $activity->causer->name ?? 'Sistem' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $activity->description }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Tidak ada aktivitas yang cocok dengan filter Anda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>