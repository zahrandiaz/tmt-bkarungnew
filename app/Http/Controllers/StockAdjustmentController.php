<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    /**
     * Menampilkan halaman form penyesuaian stok dan riwayatnya.
     */
    public function index()
    {
        $adjustments = StockAdjustment::with(['product', 'user'])
                        ->latest()
                        ->paginate(15);

        return view('stock-adjustments.index', compact('adjustments'));
    }

    /**
     * Menyimpan penyesuaian stok baru.
     */
    public function store(StoreStockAdjustmentRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated) {
                $product = Product::findOrFail($validated['product_id']);
                
                // Buat catatan di riwayat penyesuaian
                StockAdjustment::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'type' => $validated['type'],
                    'quantity' => $validated['quantity'],
                    'reason' => $validated['reason'],
                ]);

                // Perbarui stok produk
                if ($validated['type'] === 'increment') {
                    $product->increment('stock', $validated['quantity']);
                } else {
                    $product->decrement('stock', $validated['quantity']);
                }
            });

            return redirect()->route('stock-adjustments.index')->with('success', 'Penyesuaian stok berhasil disimpan.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
        }
    }
}