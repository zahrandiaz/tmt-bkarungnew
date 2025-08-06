<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockExport implements FromQuery, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Database\Query\Builder
    */
    public function query()
    {
        return Product::query()->with(['category', 'type'])->orderBy('name', 'asc');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'SKU',
            'Nama Produk',
            'Kategori',
            'Jenis',
            'Stok Saat Ini',
            'Batas Stok Minimum',
            'Status Stok',
            'Harga Beli (Rp)',
            'Harga Jual (Rp)',
        ];
    }

    /**
     * @param mixed $product
     * @return array
     */
    public function map($product): array
    {
        $statusText = 'Aman';
        if ($product->stock <= 0) {
            $statusText = 'Habis';
        } elseif ($product->stock <= $product->min_stock_level) {
            $statusText = 'Stok Menipis';
        }

        return [
            $product->sku,
            $product->name,
            $product->category->name ?? 'N/A',
            $product->type->name ?? 'N/A',
            $product->stock,
            $product->min_stock_level,
            $statusText,
            $product->purchase_price,
            $product->selling_price,
        ];
    }
}