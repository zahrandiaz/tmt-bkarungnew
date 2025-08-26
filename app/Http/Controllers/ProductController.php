<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $productsQuery = Product::with(['category', 'type']);

        if ($search) {
            $productsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $productsQuery->paginate(10)->appends(['search' => $search]);

        return view('products.index', compact('products', 'search'));
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
        
        // [PERBAIKAN] Hanya buat SKU jika tidak diisi. Jika diisi, gunakan nilai dari form.
        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-' . strtoupper(Str::random(8));
        }

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
        
        // [PERBAIKAN] Terapkan logika yang sama untuk update
        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-' . strtoupper(Str::random(8));
        }
        
        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $image = $request->file('image');
            $fileName = time() . '_' . Str::random(10) . '.webp';
            $imageCompressed = Image::read($image->getRealPath())->toWebp(75);
            Storage::disk('public')->put('products/' . $fileName, (string) $imageCompressed);
            $validated['image_path'] = 'products/' . $fileName;
        }
        
        $product->update($validated);
        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
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

    public function search(Request $request)
    {
        $searchTerm = $request->query('q', '');
        $products = Product::where('is_active', true)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('sku', 'like', "%{$searchTerm}%");
            })
            ->select('id', 'name', 'selling_price', 'stock', 'sku') 
            ->limit(20)
            ->get();

        return response()->json(['products' => $products]);
    }

    public function gallery(Request $request)
    {
        $products = Product::where('is_active', true)
            ->whereNotNull('image_path')
            ->select('id', 'name', 'selling_price', 'stock', 'image_path')
            ->latest()
            ->paginate(12);

        return response()->json($products);
    }
}