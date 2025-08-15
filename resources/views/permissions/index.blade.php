<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Hak Akses') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('permissions.update') }}" method="POST">
                @csrf
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        @if (session('success'))
                            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif
                        
                        <p class="text-gray-600 mb-6">Atur hak akses untuk setiap peran. Peran "Admin" memiliki semua hak akses secara default dan tidak dapat diubah dari halaman ini.</p>
                        
                        <div class="space-y-8">
                            @foreach ($roles as $role)
                                <div class="border rounded-lg p-6">
                                    <h3 class="text-xl font-bold text-gray-800 mb-4">{{ $role->name }}</h3>
                                    
                                    <div class="space-y-6">
                                        @foreach ($permissions as $group => $permissionList)
                                            <div>
                                                <h4 class="font-semibold text-md text-gray-700 capitalize border-b pb-2 mb-3">{{ str_replace('-', ' ', $group) }}</h4>
                                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                                    @foreach($permissionList as $permission)
                                                        <label for="permission-{{ $role->id }}-{{ $permission->id }}" class="flex items-center space-x-3">
                                                            <input type="checkbox" 
                                                                   name="permissions[{{ $role->id }}][]" 
                                                                   value="{{ $permission->name }}"
                                                                   id="permission-{{ $role->id }}-{{ $permission->id }}"
                                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                                   {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                            <span class="text-sm text-gray-600">{{ str_replace('-', ' ', $permission->name) }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>