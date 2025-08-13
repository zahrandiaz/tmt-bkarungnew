<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Transaksi Penjualan') }}
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
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-4">
                        <a href="{{ route('sales.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Tambah Penjualan
                        </a>
                    </div>
                    
                    <div class="mb-4 border-b border-gray-200">
                        <nav class="flex -mb-px space-x-4" aria-label="Tabs">
                                <a href="{{ route('sales.index', ['status' => 'selesai']) }}" class="px-3 py-2 font-medium text-sm rounded-md {{ request('status', 'selesai') == 'selesai' ? 'bg-gray-800 text-white' : 'text-gray-500 hover:text-gray-700' }}">
                                    Selesai
                                </a>
                                <a href="{{ route('sales.index', ['status' => 'semua']) }}" class="px-3 py-2 font-medium text-sm rounded-md {{ request('status') == 'semua' ? 'bg-gray-800 text-white' : 'text-gray-500 hover:text-gray-700' }}">
                                    Semua
                                </a>
                                <a href="{{ route('sales.index', ['status' => 'dibatalkan']) }}" class="px-3 py-2 font-medium text-sm rounded-md {{ request('status') == 'dibatalkan' ? 'bg-gray-800 text-white' : 'text-gray-500 hover:text-gray-700' }}">
                                    Dibatalkan
                                </a>
                        </nav>
                    </div>

                    {{-- [MODIFIKASI V1.9.0] Tabel dengan Status Bayar & Transaksi Terpisah --}}
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">No</th>
                                    <th scope="col" class="px-6 py-3">No. Invoice</th>
                                    <th scope="col" class="px-6 py-3">Tanggal</th>
                                    <th scope="col" class="px-6 py-3">Pelanggan</th>
                                    <th scope="col" class="px-6 py-3">Total</th>
                                    <th scope="col" class="px-6 py-3">Status Bayar</th>
                                    <th scope="col" class="px-6 py-3">Status Transaksi</th>
                                    <th scope="col" class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sales as $sale)
                                <tr class="bg-white border-b hover:bg-gray-50 {{ $sale->trashed() ? 'bg-red-50' : '' }}">
                                    <td class="px-6 py-4">{{ ($sales->currentPage() - 1) * $sales->perPage() + $loop->iteration }}</td>
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $sale->invoice_number }}</th>
                                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($sale->sale_date)->isoFormat('D MMM YYYY, HH:mm') }}</td>
                                    <td class="px-6 py-4">{{ $sale->customer->name }}</td>
                                    <td class="px-6 py-4">{{ 'Rp ' . number_format($sale->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4">
                                        @if ($sale->payment_status == 'Lunas')
                                            <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">Lunas</span>
                                        @elseif ($sale->payment_status == 'Belum Lunas')
                                            <span class="px-2 py-1 font-semibold leading-tight text-yellow-700 bg-yellow-100 rounded-full">Belum Lunas</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($sale->trashed())
                                            <span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full">Dibatalkan</span>
                                        @else
                                            <span class="px-2 py-1 font-semibold leading-tight text-blue-700 bg-blue-100 rounded-full">Selesai</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-left flex items-center space-x-2">
                                        @if ($sale->trashed())
                                            @role('Admin')
                                                <form action="{{ route('sales.restore', $sale->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin memulihkan transaksi ini?')">
                                                    @csrf
                                                    <button type="submit" class="font-medium text-green-600 hover:underline">Pulihkan</button>
                                                </form>
                                            @endrole
                                        @else
                                            <a href="{{ route('sales.show', $sale->id) }}" class="font-medium text-blue-600 hover:underline">Detail</a>
                                            @if ($sale->payment_status == 'Belum Lunas')
                                                @role('Admin|Manager')
                                                    <a href="{{ route('receivables.manage', $sale->id) }}" class="font-medium text-yellow-600 hover:underline">Kelola Bayar</a>
                                                @endrole
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr class="bg-white border-b">
                                    <td colspan="8" class="px-6 py-4 text-center">
                                        Belum ada data transaksi penjualan pada tab ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $sales->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>