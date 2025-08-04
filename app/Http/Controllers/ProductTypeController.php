<?php

namespace App\Http\Controllers;

use App\Models\ProductType; // <-- 1. TAMBAHKAN INI
use Illuminate\Http\Request;

class ProductTypeController extends Controller
{
    public function index()
    {
        $types = ProductType::all();
        return view('product-types.index', compact('types'));
    }

    public function create()
    {
        return view('product-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|unique:karung_product_types,name|max:255']);
        ProductType::create($validated);
        return redirect()->route('product-types.index')->with('success', 'Jenis produk berhasil ditambahkan.');
    }

    public function edit(ProductType $productType)
    {
        return view('product-types.edit', compact('productType'));
    }

    public function update(Request $request, ProductType $productType)
    {
        $validated = $request->validate(['name' => 'required|string|unique:karung_product_types,name,' . $productType->id . '|max:255']);
        $productType->update($validated);
        return redirect()->route('product-types.index')->with('success', 'Jenis produk berhasil diperbarui.');
    }

    public function destroy(ProductType $productType)
    {
        $productType->delete();
        return redirect()->route('product-types.index')->with('success', 'Jenis produk berhasil dihapus.');
    }
}