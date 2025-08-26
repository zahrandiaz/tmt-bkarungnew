<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Sistem') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    {{-- [DIUBAH] Menggunakan blok notifikasi standar --}}
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md" role="alert">
                            Terjadi kesalahan validasi. Silakan periksa kembali input Anda.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        
                        <div class="space-y-8">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Pengaturan Umum Toko</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Informasi ini akan ditampilkan pada semua dokumen cetak seperti invoice dan laporan.
                                </p>
                                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                    <div class="sm:col-span-4">
                                        <label for="store_name" class="block text-sm font-medium text-gray-700">Nama Toko</label>
                                        <input type="text" name="store_name" id="store_name" value="{{ $settings['store_name'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <x-input-error :messages="$errors->get('store_name')" class="mt-2" />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="store_phone" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                                        <input type="text" name="store_phone" id="store_phone" value="{{ $settings['store_phone'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <x-input-error :messages="$errors->get('store_phone')" class="mt-2" />
                                    </div>

                                    <div class="sm:col-span-6">
                                        <label for="store_address" class="block text-sm font-medium text-gray-700">Alamat Toko</label>
                                        <textarea id="store_address" name="store_address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $settings['store_address'] ?? '' }}</textarea>
                                        <x-input-error :messages="$errors->get('store_address')" class="mt-2" />
                                    </div>

                                    <div class="sm:col-span-6">
                                        <label for="invoice_footer_notes" class="block text-sm font-medium text-gray-700">Catatan Kaki Nota/Invoice</label>
                                        <textarea id="invoice_footer_notes" name="invoice_footer_notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $settings['invoice_footer_notes'] ?? '' }}</textarea>
                                        <x-input-error :messages="$errors->get('invoice_footer_notes')" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-8">
                                <h3 class="text-lg font-medium text-gray-900">Manajemen Stok</h3>
                                <div class="mt-4">
                                    <div class="relative flex items-start">
                                        <div class="flex h-6 items-center">
                                            <input id="enable_automatic_stock" name="enable_automatic_stock" type="checkbox" value="1" 
                                                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                                   @if(isset($settings['enable_automatic_stock']) && $settings['enable_automatic_stock'] == '1') checked @endif>
                                        </div>
                                        <div class="ml-3 text-sm leading-6">
                                            <label for="enable_automatic_stock" class="font-medium text-gray-900">Aktifkan Manajemen Stok Otomatis</label>
                                            <p class="text-gray-500">Jika aktif, stok akan otomatis bertambah saat pembelian dan berkurang saat penjualan.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end pt-6 border-t border-gray-200">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Simpan Pengaturan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>