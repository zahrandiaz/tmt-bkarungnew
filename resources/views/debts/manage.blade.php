<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Pembayaran Utang') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Notifikasi --}}
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- [MODIFIKASI V2.0.0] Bungkus form dengan @can --}}
                @can('finance-manage-payment')
                {{-- Kolom Kiri: Form Pembayaran --}}
                <div class="md:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Catat Pembayaran Baru</h3>
                            <form method="POST" action="{{ route('debts.payments.store', $purchase) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="mt-4">
                                    <x-input-label for="amount" :value="__('Jumlah Bayar')" />
                                    <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount" :value="old('amount')" required autofocus />
                                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="payment_date" :value="__('Tanggal Bayar')" />
                                    <x-text-input id="payment_date" class="block mt-1 w-full" type="date" name="payment_date" :value="old('payment_date', now()->format('Y-m-d'))" required />
                                    <x-input-error :messages="$errors->get('payment_date')" class="mt-2" />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="payment_method" :value="__('Metode Pembayaran')" />
                                    <select id="payment_method" name="payment_method" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                        <option value="tunai" {{ old('payment_method') == 'tunai' ? 'selected' : '' }}>Tunai</option>
                                        <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                                        <option value="lainnya" {{ old('payment_method') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="attachment" :value="__('Bukti Bayar (Opsional)')" />
                                    <input type="file" name="attachment" id="attachment" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 mt-1">
                                    <x-input-error :messages="$errors->get('attachment')" class="mt-2" />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="notes" :value="__('Catatan (Opsional)')" />
                                    <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                                </div>

                                <div class="flex items-center justify-end mt-6">
                                    <x-primary-button>
                                        {{ __('Simpan Pembayaran') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endcan

                {{-- Kolom Kanan: Detail & Riwayat --}}
                {{-- [MODIFIKASI V2.0.0] Sesuaikan lebar kolom jika form disembunyikan --}}
                <div class="@can('finance-manage-payment') md:col-span-2 @else md:col-span-3 @endcan">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Ringkasan Utang</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between"><span class="text-gray-500">Kode Pembelian:</span> <span class="font-semibold">{{ $purchase->purchase_code }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Supplier:</span> <span class="font-semibold">{{ $purchase->supplier->name }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Tanggal Transaksi:</span> <span class="font-semibold">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y, H:i') }}</span></div>
                                <hr class="my-2">
                                <div class="flex justify-between text-base"><span class="text-gray-500">Total Tagihan:</span> <span class="font-bold">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Sudah Dibayar:</span> <span class="font-semibold text-green-600">Rp {{ number_format($purchase->total_paid, 0, ',', '.') }}</span></div>
                                <div class="flex justify-between text-lg"><span class="text-gray-500">Sisa Tagihan:</span> <span class="font-bold text-red-600">Rp {{ number_format($purchase->total_amount - $purchase->total_paid, 0, ',', '.') }}</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Riwayat Pembayaran</h3>
                            <div class="relative overflow-x-auto">
                                <table class="w-full text-sm text-left text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3">Tanggal</th>
                                            <th class="px-4 py-3 text-right">Jumlah</th>
                                            <th class="px-4 py-3">Metode</th>
                                            <th class="px-4 py-3">Dicatat Oleh</th>
                                            <th class="px-4 py-3">Bukti</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($purchase->payments->sortByDesc('payment_date') as $payment)
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-4 py-3">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                                <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                                <td class="px-4 py-3">{{ Str::title($payment->payment_method) }}</td>
                                                <td class="px-4 py-3">{{ $payment->user->name ?? 'N/A' }}</td>
                                                <td class="px-4 py-3">
                                                    @if($payment->attachment_path)
                                                        <a href="{{ asset('storage/' . $payment->attachment_path) }}" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-gray-500">Belum ada riwayat pembayaran.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>