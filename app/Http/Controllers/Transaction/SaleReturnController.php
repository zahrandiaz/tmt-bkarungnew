<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\SaleReturn;
use App\Models\SaleReturnDetail;
use App\Models\Product;
use App\Models\Setting;
use App\Http\Requests\StoreSaleReturnRequest; // [BARU] Import Form Request
use Illuminate\Support\Facades\DB; // [BARU] Import DB Facade

class SaleReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $saleReturns = SaleReturn::with('sale.customer', 'user') // Load relasi customer juga
            ->latest()
            ->paginate(10);

        return view('sale-returns.index', compact('saleReturns'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sales = \App\Models\Sale::whereNull('deleted_at')->latest()->get();
        return view('sale-returns.create', compact('sales'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleReturnRequest $request)
    {
        $validated = $request->validated();
        $items = $validated['items'];
        $totalAmount = collect($items)->sum(function ($item) {
            return $item['return_quantity'] * $item['unit_price'];
        });

        // Cek apakah manajemen stok aktif
        $stockManagementSetting = Setting::where('key', 'manage_stock')->first();
        $isStockManaged = $stockManagementSetting ? $stockManagementSetting->value : true; // Default true jika setting tidak ada

        try {
            DB::transaction(function () use ($validated, $items, $totalAmount, $isStockManaged) {
                // 1. Buat data master retur
                $saleReturn = SaleReturn::create([
                    'sale_id' => $validated['sale_id'],
                    'user_id' => auth()->id(),
                    'return_code' => 'RTS-' . date('Ymd') . '-' . str_pad(SaleReturn::count() + 1, 4, '0', STR_PAD_LEFT),
                    'return_date' => $validated['return_date'],
                    'total_amount' => $totalAmount,
                    'notes' => $validated['notes'],
                ]);

                // 2. Loop dan simpan detail item retur, lalu update stok
                foreach ($items as $item) {
                    SaleReturnDetail::create([
                        'sale_return_id' => $saleReturn->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['return_quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['return_quantity'] * $item['unit_price'],
                    ]);

                    // 3. Tambah kembali stok produk jika manajemen stok aktif
                    if ($isStockManaged) {
                        $product = Product::find($item['product_id']);
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


    /**
     * Display the specified resource.
     */
    public function show(SaleReturn $saleReturn)
    {
        // Muat semua relasi yang dibutuhkan untuk ditampilkan di halaman detail
        $saleReturn->load('sale.customer', 'user', 'details.product');
        return view('sale-returns.show', compact('saleReturn'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SaleReturn $saleReturn)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSaleReturnRequest $request, SaleReturn $saleReturn)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SaleReturn $saleReturn)
    {
        //
    }
}