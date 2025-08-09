<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use Illuminate\Support\Str;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'type'])->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = ProductCategory::all();
        $types = ProductType::all();
        return view('products.create', compact('categories', 'types'));
    }

    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();
        $validated['sku'] = 'SKU-' . strtoupper(Str::random(8));

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = time() . '_' . Str::random(10) . '.webp';

            $imageCompressed = Image::read($image->getRealPath())->toWebp(75);
            Storage::disk('public')->put('products/' . $fileName, (string) $imageCompressed);
            
            $validated['image_path'] = 'products/' . $fileName;
        }
        
        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::all();
        $types = ProductType::all();
        return view('products.edit', compact('product', 'categories', 'types'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        // [BARU] Logika untuk memperbarui gambar
        if ($request->hasFile('image')) {
            // 1. Hapus gambar lama jika ada
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            // 2. Simpan gambar baru
            $image = $request->file('image');
            $fileName = time() . '_' . Str::random(10) . '.webp';
            $imageCompressed = Image::read($image->getRealPath())->toWebp(75);
            Storage::disk('public')->put('products/' . $fileName, (string) $imageCompressed);

            // 3. Tambahkan path gambar baru ke data yang akan diupdate
            $validated['image_path'] = 'products/' . $fileName;
        }

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        // [BARU] Hapus gambar dari storage saat produk dihapus
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        
        $inSale = DB::table('sale_details')->where('product_id', $product->id)->exists();
        $inPurchase = DB::table('purchase_details')->where('product_id', $product->id)->exists();

        if ($inSale || $inPurchase) {
            return redirect()->route('products.index')->with('error', 'Produk tidak dapat dihapus karena sudah tercatat dalam transaksi.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}