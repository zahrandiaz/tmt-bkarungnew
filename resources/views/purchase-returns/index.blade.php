<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Retur Pembelian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-200 rounded-md">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                 <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-200 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-end mb-4">
                        @can('transaction-create')
                        <a href="{{ route('purchase-returns.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('+ Tambah Retur') }}
                        </a>
                        @endcan
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                            <thead class="ltr:text-left rtl:text-right">
                                <tr>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">No.</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Kode Retur</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Kode Pembelian</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Tanggal Retur</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Total</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Dicatat Oleh</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Aksi</th> {{-- [UBAH] --}}
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($purchaseReturns as $return)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">{{ $loop->iteration + $purchaseReturns->firstItem() - 1 }}</td>
                                    <td class="whitespace-nowrap px-4 py-2 text-gray-700">{{ $return->return_code }}</td>
                                    <td class="whitespace-nowrap px-4 py-2 text-gray-700">{{ $return->purchase->purchase_code }}</td>
                                    <td class="whitespace-nowrap px-4 py-2 text-gray-700">{{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y H:i') }}</td>
                                    <td class="whitespace-nowrap px-4 py-2 text-gray-700">Rp {{ number_format($return->total_amount, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-4 py-2 text-gray-700">{{ $return->user->name }}</td>
                                    <td class="whitespace-nowrap px-4 py-2 flex items-center space-x-2"> {{-- [UBAH] --}}
                                        <a href="{{ route('purchase-returns.show', $return->id) }}" class="inline-block rounded bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700">
                                            Detail
                                        </a>
                                        {{-- [TAMBAHKAN BLOK INI] --}}
                                        @can('return-delete')
                                        <form action="{{ route('purchase-returns.destroy', $return->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin membatalkan retur ini? Stok akan disesuaikan kembali.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-block rounded bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700">
                                                Batalkan
                                            </button>
                                        </form>
                                        @endcan
                                        {{-- [AKHIR BLOK TAMBAHAN] --}}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-gray-500">Tidak ada data retur pembelian.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $purchaseReturns->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>