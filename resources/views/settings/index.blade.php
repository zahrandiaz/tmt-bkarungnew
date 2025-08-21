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
                    
                    @if (session('status') === 'pengaturan-berhasil-disimpan')
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Sukses!</strong>
                            <span class="block sm:inline">Pengaturan berhasil disimpan.</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Manajemen Stok</h3>
                                <div class="mt-4 border-t border-gray-200 pt-4">
                                    <div class="relative flex items-start">
                                        <div class="flex h-6 items-center">
                                            <input id="enable_automatic_stock" name="enable_automatic_stock" type="checkbox" value="1" 
                                                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                                   {{-- Cek apakah pengaturan ada dan nilainya '1' --}}
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