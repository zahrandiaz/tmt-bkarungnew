<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="relative min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center">
        <div class="text-center p-8">
            <header class="mb-8">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800 dark:text-gray-100">
                    TMT Bagja Karung
                </h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                    Sistem pendataan operasional toko karung, mencakup pencatatan transaksi, manajemen data, dan laporan.
                </p>
            </header>

            <main>
                <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center w-full sm:w-auto px-8 py-3 text-base font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                            Login
                        </a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center w-full sm:w-auto px-8 py-3 text-base font-medium text-indigo-700 bg-indigo-100 border border-transparent rounded-md hover:bg-indigo-200 dark:text-indigo-300 dark:bg-gray-800 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                            Register
                        </a>
                    @endif
                </div>
            </main>

            <footer class="absolute bottom-6 text-sm text-gray-500 dark:text-gray-400">
                <p>Versi Pengembangan {{ config('app.version', '1.12.0') }}</p>
            </footer>
        </div>
    </div>
</body>

</html>