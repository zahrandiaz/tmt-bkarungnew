<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Piutang') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- [BARU] Navigasi Tab --}}
                    <div class="mb-4 border-b border-gray-200">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                            <li class="me-2">
                                <a href="{{ route('receivables.index', ['status' => 'belum lunas']) }}" 
                                   class="inline-block p-4 border-b-2 rounded-t-lg {{ request('status', 'belum lunas') == 'belum lunas' ? 'text-blue-600 border-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                                   Belum Lunas
                                </a>
                            </li>
                            <li class="me-2">
                                <a href="{{ route('receivables.index', ['status' => 'lunas']) }}" 
                                   class="inline-block p-4 border-b-2 rounded-t-lg {{ request('status') == 'lunas' ? 'text-blue-600 border-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                                   Lunas
                                </a>
                            </li>
                        </ul>
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
                                    <th scope="col" class="px-6 py-3">No. Invoice</th>
                                    <th scope="col" class="px-6 py-3">Nama Pelanggan</th>
                                    <th scope="col" class="px-6 py-3">Tanggal Penjualan</th>
                                    <th scope="col" class="px-6 py-3 text-right">Total Tagihan</th>
                                    <th scope="col" class="px-6 py-3 text-right">Sudah Dibayar</th>
                                    {{-- [UBAH] Tampilkan Sisa Tagihan hanya jika status belum lunas --}}
                                    @if(request('status', 'belum lunas') == 'belum lunas')
                                    <th scope="col" class="px-6 py-3 text-right">Sisa Tagihan</th>
                                    @endif
                                    <th scope="col" class="px-6 py-3">
                                        <span class="sr-only">Aksi</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($receivables as $index => $sale)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">{{ $receivables->firstItem() + $index }}</td>
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $sale->invoice_number }}
                                        </td>
                                        <td class="px-6 py-4">{{ $sale->customer->name }}</td>
                                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y H:i') }}</td>
                                        <td class="px-6 py-4 text-right">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-right">Rp {{ number_format($sale->total_paid, 0, ',', '.') }}</td>
                                        {{-- [UBAH] Tampilkan Sisa Tagihan hanya jika status belum lunas --}}
                                        @if(request('status', 'belum lunas') == 'belum lunas')
                                        <td class="px-6 py-4 text-right font-bold text-red-600">
                                            Rp {{ number_format($sale->total_amount - $sale->total_paid, 0, ',', '.') }}
                                        </td>
                                        @endif
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('receivables.manage', $sale->id) }}" class="font-medium text-blue-600 hover:underline">Kelola</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b">
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                            Tidak ada data piutang untuk status ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $receivables->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>