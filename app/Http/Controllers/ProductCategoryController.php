<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
// Hapus 'use Illuminate\Http\Request;'
use App\Http\Requests\StoreProductCategoryRequest; // <-- [BARU]
use App\Http\Requests\UpdateProductCategoryRequest; // <-- [BARU]

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::paginate(10); // [MODIFIKASI] Tambahkan paginasi
        return view('product-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('product-categories.create');
    }

    // [MODIFIKASI] Gunakan StoreProductCategoryRequest
    public function store(StoreProductCategoryRequest $request)
    {
        ProductCategory::create($request->validated());
        return redirect()->route('product-categories.index')->with('success', 'Kategori produk berhasil ditambahkan.');
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('product-categories.edit', compact('productCategory'));
    }

    // [MODIFIKASI] Gunakan UpdateProductCategoryRequest
    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory)
    {
        $productCategory->update($request->validated());
        return redirect()->route('product-categories.index')->with('success', 'Kategori produk berhasil diperbarui.');
    }

    // [MODIFIKASI] Tambahkan validasi sebelum hapus
    public function destroy(ProductCategory $productCategory)
    {
        // Cek apakah ada produk yang masih menggunakan kategori ini
        if ($productCategory->products()->exists()) {
            return redirect()->route('product-categories.index')->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh produk.');
        }

        $productCategory->delete();
        return redirect()->route('product-categories.index')->with('success', 'Kategori produk berhasil dihapus.');
    }
}