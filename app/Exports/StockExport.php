<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * [BARU] Properti untuk menyimpan kata kunci pencarian.
     * @var string|null
     */
    protected $search;

    /**
     * [BARU] Constructor untuk menerima kata kunci pencarian.
     * @param string|null $search
     */
    public function __construct($search = null)
    {
        $this->search = $search;
    }

    /**
     * [DIPERBARUI] Menerapkan filter pencarian pada query.
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        $query = Product::query()->with(['category', 'type']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('name', 'asc');
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