<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory; // <-- 1. TAMBAHKAN INI
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::all();
        return view('product-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('product-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|unique:karung_product_categories,name|max:255']);
        ProductCategory::create($validated);
        return redirect()->route('product-categories.index')->with('success', 'Kategori produk berhasil ditambahkan.');
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('product-categories.edit', compact('productCategory'));
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $validated = $request->validate(['name' => 'required|string|unique:karung_product_categories,name,' . $productCategory->id . '|max:255']);
        $productCategory->update($validated);
        return redirect()->route('product-categories.index')->with('success', 'Kategori produk berhasil diperbarui.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        // Tambahkan validasi di sini jika kategori tidak boleh dihapus jika sudah digunakan oleh produk
        $productCategory->delete();
        return redirect()->route('product-categories.index')->with('success', 'Kategori produk berhasil dihapus.');
    }
}