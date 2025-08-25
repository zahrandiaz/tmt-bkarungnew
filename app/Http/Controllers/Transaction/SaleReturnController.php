<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\SaleReturn;
use App\Models\SaleReturnDetail;
use App\Models\Product;
use App\Models\Setting;
use App\Http\Requests\StoreSaleReturnRequest;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function index()
    {
        $saleReturns = SaleReturn::with('sale.customer', 'user')->latest()->paginate(10);
        return view('sale-returns.index', compact('saleReturns'));
    }

    public function create()
    {
        return view('sale-returns.create');
    }

    public function store(StoreSaleReturnRequest $request)
    {
        $validated = $request->validated();
        $items = $validated['items'];
        $totalAmount = collect($items)->sum(fn($item) => $item['return_quantity'] * $item['unit_price']);

        try {
            DB::transaction(function () use ($validated, $items, $totalAmount) {
                // [PERBAIKAN #2 & #3] Ambil pengaturan & produk di luar loop
                $isStockManaged = Setting::where('key', 'enable_automatic_stock')->first()->value ?? '0';
                $productIds = array_column($items, 'product_id');
                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

                // [PERBAIKAN #1] Logika pembuatan kode retur yang aman dari race condition
                $latestReturnId = SaleReturn::latest('id')->first()?->id ?? 0;
                $returnCode = 'RTS-' . now()->format('Ymd') . '-' . str_pad($latestReturnId + 1, 4, '0', STR_PAD_LEFT);

                $saleReturn = SaleReturn::create([
                    'sale_id' => $validated['sale_id'],
                    'user_id' => auth()->id(),
                    'return_code' => $returnCode,
                    'return_date' => $validated['return_date'],
                    'total_amount' => $totalAmount,
                    'notes' => $validated['notes'],
                ]);

                foreach ($items as $item) {
                    SaleReturnDetail::create([
                        'sale_return_id' => $saleReturn->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['return_quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['return_quantity'] * $item['unit_price'],
                    ]);

                    if ($isStockManaged === '1') {
                        $product = $products->get($item['product_id']);
                        if ($product) {
                            $product->increment('stock', $item['return_quantity']);
                        }
                    }
                }
            });
            return redirect()->route('sale-returns.index')->with('success', 'Retur penjualan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load('sale.customer', 'user', 'details.product');
        return view('sale-returns.show', compact('saleReturn'));
    }
    
    // [PERBAIKAN #4] Implementasi method destroy untuk pembatalan retur
    public function destroy(SaleReturn $saleReturn)
    {
        try {
            DB::transaction(function () use ($saleReturn) {
                $isStockManaged = Setting::where('key', 'enable_automatic_stock')->first()->value ?? '0';

                if ($isStockManaged === '1') {
                    // Balikkan logika stok: kurangi stok saat retur penjualan dibatalkan
                    foreach ($saleReturn->details as $detail) {
                        $product = $detail->product;
                        if ($product && $product->stock < $detail->quantity) {
                            throw new \Exception("Stok '{$product->name}' tidak cukup untuk membatalkan retur ini.");
                        }
                        if ($product) {
                            $product->decrement('stock', $detail->quantity);
                        }
                    }
                }
                
                $saleReturn->delete(); // Hapus retur dan detailnya (via cascade)
            });

            return redirect()->route('sale-returns.index')->with('success', 'Retur penjualan berhasil dibatalkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan retur: ' . $e->getMessage());
        }
    }
}