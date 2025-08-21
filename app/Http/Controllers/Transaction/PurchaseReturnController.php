<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Models\Product;
use App\Models\Setting;
use App\Http\Requests\StorePurchaseReturnRequest; // [BARU]
use Illuminate\Support\Facades\DB; // [BARU]

class PurchaseReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchaseReturns = PurchaseReturn::with('purchase.supplier', 'user')
            ->latest()
            ->paginate(10);

        return view('purchase-returns.index', compact('purchaseReturns'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $purchases = \App\Models\Purchase::whereNull('deleted_at')->latest()->get();
        return view('purchase-returns.create', compact('purchases'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePurchaseReturnRequest $request)
    {
        $validated = $request->validated();
        $items = $validated['items'];
        $totalAmount = collect($items)->sum(function ($item) {
            return $item['return_quantity'] * $item['unit_price'];
        });

        $stockManagementSetting = Setting::where('key', 'manage_stock')->first();
        $isStockManaged = $stockManagementSetting ? $stockManagementSetting->value : true;

        try {
            DB::transaction(function () use ($validated, $items, $totalAmount, $isStockManaged) {
                // 1. Buat data master retur
                $purchaseReturn = PurchaseReturn::create([
                    'purchase_id' => $validated['purchase_id'],
                    'user_id' => auth()->id(),
                    'return_code' => 'RTP-' . date('Ymd') . '-' . str_pad(PurchaseReturn::count() + 1, 4, '0', STR_PAD_LEFT),
                    'return_date' => $validated['return_date'],
                    'total_amount' => $totalAmount,
                    'notes' => $validated['notes'],
                ]);

                // 2. Loop dan simpan detail item, lalu update stok
                foreach ($items as $item) {
                    PurchaseReturnDetail::create([
                        'purchase_return_id' => $purchaseReturn->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['return_quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['return_quantity'] * $item['unit_price'],
                    ]);

                    // 3. Kurangi stok produk jika manajemen stok aktif
                    if ($isStockManaged) {
                        $product = Product::find($item['product_id']);
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


    /**
     * Display the specified resource.
     */
    public function show(PurchaseReturn $purchaseReturn)
    {
        // Muat semua relasi yang dibutuhkan untuk ditampilkan di halaman detail
        $purchaseReturn->load('purchase.supplier', 'user', 'details.product');
        return view('purchase-returns.show', compact('purchaseReturn'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseReturn $purchaseReturn)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePurchaseReturnRequest $request, PurchaseReturn $purchaseReturn)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseReturn $purchaseReturn)
    {
        //
    }
}