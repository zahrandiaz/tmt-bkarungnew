<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok Produk</title>
    <style>
        body { font-family: 'sans-serif'; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { margin-top: 20px; font-size: 9px; text-align: center; color: #888; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $settings['store_name'] ?? 'Toko Anda' }}</h1>
        <p>{{ $settings['store_address'] ?? 'Alamat Toko Anda' }}</p>
        <p>Telepon: {{ $settings['store_phone'] ?? 'Nomor Telepon Anda' }}</p>
        <hr>
        <h2 style="margin-top: 15px; font-size: 16px;">Laporan Stok Produk</h2>
        <p style="font-size: 11px;">Dicetak pada: {{ now()->isoFormat('D MMMM YYYY, HH:mm') }}</p>
        @if($search)
            <p style="font-size: 11px;"><strong>Filter Pencarian:</strong> "{{ $search }}"</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th class="text-center">Stok Saat Ini</th>
                <th class="text-center">Status Stok</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $product->stock }}</td>
                    <td class="text-center">
                        @php
                            $statusText = 'Aman';
                            if ($product->stock <= 0) {
                                $statusText = 'Habis';
                            } elseif ($product->stock <= $product->min_stock_level) {
                                $statusText = 'Stok Menipis';
                            }
                        @endphp
                        {{ $statusText }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data produk yang cocok.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Laporan ini dibuat secara otomatis oleh sistem.
    </div>
</body>
</html>