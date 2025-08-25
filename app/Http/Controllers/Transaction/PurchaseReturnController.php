<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Models\Product;
use App\Models\Setting;
use App\Http\Requests\StorePurchaseReturnRequest;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        $purchaseReturns = PurchaseReturn::with('purchase.supplier', 'user')->latest()->paginate(10);
        return view('purchase-returns.index', compact('purchaseReturns'));
    }

    public function create()
    {
        return view('purchase-returns.create');
    }

    public function store(StorePurchaseReturnRequest $request)
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
                $latestReturnId = PurchaseReturn::latest('id')->first()?->id ?? 0;
                $returnCode = 'RTP-' . now()->format('Ymd') . '-' . str_pad($latestReturnId + 1, 4, '0', STR_PAD_LEFT);

                $purchaseReturn = PurchaseReturn::create([
                    'purchase_id' => $validated['purchase_id'],
                    'user_id' => auth()->id(),
                    'return_code' => $returnCode,
                    'return_date' => $validated['return_date'],
                    'total_amount' => $totalAmount,
                    'notes' => $validated['notes'],
                ]);

                foreach ($items as $item) {
                    PurchaseReturnDetail::create([
                        'purchase_return_id' => $purchaseReturn->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['return_quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['return_quantity'] * $item['unit_price'],
                    ]);

                    if ($isStockManaged === '1') {
                        $product = $products->get($item['product_id']);
                        if ($product && $product->stock < $item['return_quantity']) {
                            throw new \Exception("Stok '{$product->name}' tidak cukup untuk diretur.");
                        }
                        if ($product) {
                            $product->decrement('stock', $item['return_quantity']);
                        }
                    }
                }
            });
            return redirect()->route('purchase-returns.index')->with('success', 'Retur pembelian berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load('purchase.supplier', 'user', 'details.product');
        return view('purchase-returns.show', compact('purchaseReturn'));
    }

    // [PERBAIKAN #4] Implementasi method destroy untuk pembatalan retur
    public function destroy(PurchaseReturn $purchaseReturn)
    {
        try {
            DB::transaction(function () use ($purchaseReturn) {
                $isStockManaged = Setting::where('key', 'enable_automatic_stock')->first()->value ?? '0';
                
                if ($isStockManaged === '1') {
                    // Balikkan logika stok: tambah stok saat retur pembelian dibatalkan
                    foreach ($purchaseReturn->details as $detail) {
                        if ($detail->product) {
                            $detail->product->increment('stock', $detail->quantity);
                        }
                    }
                }

                $purchaseReturn->delete(); // Hapus retur dan detailnya
            });

            return redirect()->route('purchase-returns.index')->with('success', 'Retur pembelian berhasil dibatalkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan retur: ' . $e->getMessage());
        }
    }
}