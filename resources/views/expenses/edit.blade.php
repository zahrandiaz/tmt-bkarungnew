<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Biaya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Formulir Biaya Operasional</h3>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" role="alert">
                            <strong class="font-bold">Terjadi Kesalahan!</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="expense_category_id" :value="__('Kategori Biaya')" />
                                <select id="expense_category_id" name="expense_category_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('expense_category_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="expense_date" :value="__('Tanggal Biaya')" />
                                <x-text-input id="expense_date" class="block mt-1 w-full" type="date" name="expense_date" :value="old('expense_date', $expense->expense_date)" required />
                                <x-input-error :messages="$errors->get('expense_date')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="name" :value="__('Nama/Deskripsi Biaya')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $expense->name)" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="amount" :value="__('Jumlah (Rp)')" />
                                <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount" :value="old('amount', $expense->amount)" required />
                                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="attachment" :value="__('Ganti Bukti/Nota (Opsional)')" />
                                <input type="file" name="attachment" id="attachment" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 mt-1">
                                <x-input-error :messages="$errors->get('attachment')" class="mt-2" />
                                @if($expense->attachment_path)
                                    <div class="mt-2 text-sm">
                                        Bukti saat ini: 
                                        <a href="{{ asset('storage/' . $expense->attachment_path) }}" target="_blank" class="text-blue-600 hover:underline">Lihat file</a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="notes" :value="__('Catatan (Opsional)')" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('notes', $expense->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('expenses.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Perbarui') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>