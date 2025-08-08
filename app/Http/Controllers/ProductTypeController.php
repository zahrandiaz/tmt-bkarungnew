<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
// Hapus 'use Illuminate\Http\Request;'
use App\Http\Requests\StoreProductTypeRequest; // <-- [BARU]
use App\Http\Requests\UpdateProductTypeRequest; // <-- [BARU]

class ProductTypeController extends Controller
{
    public function index()
    {
        $types = ProductType::paginate(10); // [MODIFIKASI] Tambahkan paginasi
        return view('product-types.index', compact('types'));
    }

    public function create()
    {
        return view('product-types.create');
    }

    // [MODIFIKASI] Gunakan StoreProductTypeRequest
    public function store(StoreProductTypeRequest $request)
    {
        ProductType::create($request->validated());
        return redirect()->route('product-types.index')->with('success', 'Jenis produk berhasil ditambahkan.');
    }

    public function edit(ProductType $productType)
    {
        return view('product-types.edit', compact('productType'));
    }

    // [MODIFIKASI] Gunakan UpdateProductTypeRequest
    public function update(UpdateProductTypeRequest $request, ProductType $productType)
    {
        $productType->update($request->validated());
        return redirect()->route('product-types.index')->with('success', 'Jenis produk berhasil diperbarui.');
    }

    // [MODIFIKASI] Tambahkan validasi sebelum hapus
    public function destroy(ProductType $productType)
    {
        // Cek apakah ada produk yang masih menggunakan jenis ini
        if ($productType->products()->exists()) {
            return redirect()->route('product-types.index')->with('error', 'Jenis tidak dapat dihapus karena masih digunakan oleh produk.');
        }

        $productType->delete();
        return redirect()->route('product-types.index')->with('success', 'Jenis produk berhasil dihapus.');
    }
}