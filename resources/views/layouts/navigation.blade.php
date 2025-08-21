<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @can('product-view')
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>Master Data</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                @can('role-view')
                                    <x-dropdown-link :href="route('roles.index')">{{ __('Manajemen Peran') }}</x-dropdown-link>
                                @endcan
                                @can('user-view')
                                    <x-dropdown-link :href="route('users.index')">{{ __('Manajemen Pengguna') }}</x-dropdown-link>
                                @endcan
                                @can('role-edit')
                                    <x-dropdown-link :href="route('permissions.index')">{{ __('Manajemen Hak Akses') }}</x-dropdown-link>
                                    <x-dropdown-link :href="route('settings.index')" :active="request()->routeIs('settings.index')">{{ __('Pengaturan') }}</x-dropdown-link>
                                @endcan
                                <x-dropdown-link :href="route('product-categories.index')">{{ __('Manajemen Kategori Produk') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('product-types.index')">{{ __('Manajemen Jenis Produk') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('products.index')">{{ __('Manajemen Produk') }}</x-dropdown-link>
                                @can('adjustment-price')
                                    <x-dropdown-link :href="route('price-adjustments.index')">{{ __('Penyesuaian Harga Jual') }}</x-dropdown-link>
                                @endcan
                                @can('adjustment-stock')
                                    <x-dropdown-link :href="route('stock-adjustments.index')">{{ __('Penyesuaian Stok') }}</x-dropdown-link>
                                @endcan
                                <x-dropdown-link :href="route('suppliers.index')">{{ __('Manajemen Supplier') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('customers.index')">{{ __('Manajemen Pelanggan') }}</x-dropdown-link>
                                @can('finance-crud-expense')
                                    <x-dropdown-link :href="route('expense-categories.index')">{{ __('Manajemen Kategori Biaya') }}</x-dropdown-link>
                                @endcan
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endcan

                    @can('transaction-view')
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>Transaksi</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('purchases.index')">{{ __('Daftar Pembelian') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('sales.index')">{{ __('Daftar Penjualan') }}</x-dropdown-link>
                                @can('transaction-create')
                                <x-dropdown-link :href="route('sales.create')">{{ __('+ Tambah Penjualan') }}</x-dropdown-link>
                                @endcan
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endcan

                    @can('finance-view')
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>Keuangan</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('receivables.index')">{{ __('Manajemen Piutang') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('debts.index')">{{ __('Manajemen Utang') }}</x-dropdown-link>
                                @can('finance-crud-expense')
                                <x-dropdown-link :href="route('expenses.index')">{{ __('Manajemen Biaya') }}</x-dropdown-link>
                                @endcan
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endcan

                    @can('report-view-all')
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>Laporan</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('reports.sales')">{{ __('Laporan Penjualan') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('reports.purchases')">{{ __('Laporan Pembelian') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('reports.stock')">{{ __('Laporan Stok Produk') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('reports.profit-loss')">{{ __('Laporan Laba Rugi') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('reports.deposits')" :active="request()->routeIs('reports.deposits')">{{ __('Laporan Setoran') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endcan
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
        </div>

        @can('product-view')
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4"><div class="font-medium text-base text-gray-800">Master Data</div></div>
            <div class="mt-3 space-y-1">
                @can('role-view')
                    <x-responsive-nav-link :href="route('roles.index')">{{ __('Manajemen Peran') }}</x-responsive-nav-link>
                @endcan
                @can('user-view')
                    <x-responsive-nav-link :href="route('users.index')">{{ __('Manajemen Pengguna') }}</x-responsive-nav-link>
                @endcan
                @can('role-edit')
                    <x-responsive-nav-link :href="route('permissions.index')">{{ __('Manajemen Hak Akses') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.index')">{{ __('Pengaturan') }}</x-responsive-nav-link>
                @endcan
                <x-responsive-nav-link :href="route('product-categories.index')">{{ __('Manajemen Kategori Produk') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('product-types.index')">{{ __('Manajemen Jenis Produk') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('products.index')">{{ __('Manajemen Produk') }}</x-responsive-nav-link>
                @can('adjustment-price')
                    <x-responsive-nav-link :href="route('price-adjustments.index')">{{ __('Penyesuaian Harga Jual') }}</x-responsive-nav-link>
                @endcan
                @can('adjustment-stock')
                    <x-responsive-nav-link :href="route('stock-adjustments.index')">{{ __('Penyesuaian Stok') }}</x-responsive-nav-link>
                @endcan
                <x-responsive-nav-link :href="route('suppliers.index')">{{ __('Manajemen Supplier') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customers.index')">{{ __('Manajemen Pelanggan') }}</x-responsive-nav-link>
                @can('finance-crud-expense')
                    <x-responsive-nav-link :href="route('expense-categories.index')">{{ __('Manajemen Kategori Biaya') }}</x-responsive-nav-link>
                @endcan
            </div>
        </div>
        @endcan

        @can('transaction-view')
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4"><div class="font-medium text-base text-gray-800">Transaksi</div></div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('purchases.index')">{{ __('Daftar Pembelian') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('sales.index')">{{ __('Daftar Penjualan') }}</x-responsive-nav-link>
                @can('transaction-create')
                <x-responsive-nav-link :href="route('sales.create')">{{ __('+ Tambah Penjualan') }}</x-responsive-nav-link>
                @endcan
            </div>
        </div>
        @endcan

        @can('finance-view')
        <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4"><div class="font-medium text-base text-gray-800">Keuangan</div></div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('receivables.index')">{{ __('Manajemen Piutang') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('debts.index')">{{ __('Manajemen Utang') }}</x-responsive-nav-link>
                    @can('finance-crud-expense')
                    <x-responsive-nav-link :href="route('expenses.index')">{{ __('Manajemen Biaya') }}</x-responsive-nav-link>
                    @endcan
                </div>
            </div>
        @endcan

        @can('report-view-all')
        <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4"><div class="font-medium text-base text-gray-800">Laporan</div></div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('reports.sales')">{{ __('Laporan Penjualan') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('reports.purchases')">{{ __('Laporan Pembelian') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('reports.stock')">{{ __('Laporan Stok Produk') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('reports.profit-loss')">{{ __('Laporan Laba Rugi') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('reports.deposits')" :active="request()->routeIs('reports.deposits')">{{ __('Laporan Setoran') }}</x-responsive-nav-link>
                </div>
            </div>
        @endcan

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>