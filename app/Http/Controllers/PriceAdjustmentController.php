<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateBulkPriceRequest; // <-- [1. TAMBAHKAN INI]

class PriceAdjustmentController extends Controller
{
    /**
     * Menampilkan halaman untuk penyesuaian harga.
     */
    public function index(Request $request)
    {
        $categories = ProductCategory::orderBy('name')->get();
        
        $productsQuery = Product::with('category')->orderBy('name');

        if ($request->filled('category_id')) {
            $productsQuery->where('product_category_id', $request->category_id);
        }

        $products = $productsQuery->paginate(50)->withQueryString();

        return view('price-adjustments.index', compact('products', 'categories'));
    }

    /**
     * Menyimpan perubahan harga massal.
     */
    // [2. GANTI SELURUH METHOD STORE DENGAN INI]
    public function store(UpdateBulkPriceRequest $request)
    {
        $validatedData = $request->validated();
        $productsToUpdate = $validatedData['products'];

        try {
            DB::transaction(function () use ($productsToUpdate) {
                foreach ($productsToUpdate as $productData) {
                    $product = Product::findOrFail($productData['id']);

                    // Aturan bisnis tambahan: Harga jual tidak boleh lebih rendah dari harga beli
                    if ($productData['selling_price'] < $product->purchase_price) {
                        // Jika aturan dilanggar, lemparkan exception untuk membatalkan transaksi
                        throw new \Exception("Harga jual untuk produk '{$product->name}' tidak boleh lebih rendah dari harga belinya (Rp " . number_format($product->purchase_price, 0, ',', '.') . ").");
                    }
                    
                    $product->update([
                        'selling_price' => $productData['selling_price']
                    ]);
                }
            });

            return redirect()->route('price-adjustments.index')->with('success', 'Harga produk berhasil diperbarui.');

        } catch (\Exception $e) {
            // Tangkap error (baik dari validasi atau lainnya) dan kembalikan ke halaman sebelumnya
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
}