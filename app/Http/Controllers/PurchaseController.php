<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Http\Requests\StorePurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Menampilkan daftar sumber daya.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        
        $query = Purchase::query();

        if ($status == 'dibatalkan') {
            $query->onlyTrashed();
        } elseif ($status == 'semua') {
            $query->withTrashed();
        } else {
            // Kasus default ('selesai' atau null) akan menampilkan yang tidak di-soft-delete
        }

        $purchases = $query->with('supplier')->latest()->paginate(10);
        
        return view('purchases.index', compact('purchases'));
    }

    /**
     * Menampilkan form untuk membuat sumber daya baru.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $products = Product::orderBy('name', 'asc')->get();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    /**
     * Menyimpan sumber daya yang baru dibuat.
     */
    public function store(StorePurchaseRequest $request)
    {
        $validatedData = $request->validated();

        try {
            DB::transaction(function () use ($validatedData, $request) {
                // [MODIFIKASI] Format Kode Pembelian Lebih Baik
                $latestPurchaseId = Purchase::withTrashed()->latest('id')->first()?->id ?? 0;
                $purchaseCode = 'PUR/' . now()->format('Ym') . '/' . str_pad($latestPurchaseId + 1, 5, '0', STR_PAD_LEFT);

                $purchase = Purchase::create([
                    'purchase_code' => $purchaseCode,
                    'reference_number' => $validatedData['reference_number'] ?? null,
                    'supplier_id' => $validatedData['supplier_id'],
                    'user_id' => $request->user()->id,
                    'purchase_date' => $validatedData['purchase_date'],
                    'total_amount' => $validatedData['total_amount'],
                    'notes' => $validatedData['notes'],
                ]);

                foreach ($validatedData['items'] as $item) {
                    $purchase->details()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'purchase_price' => $item['purchase_price'],
                    ]);
                }
            });

            return redirect()->route('purchases.index', ['status' => 'selesai'])->with('success', 'Transaksi pembelian berhasil disimpan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage())->withInput();
        }
    }


    /**
     * Menampilkan sumber daya yang spesifik.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'details.product');
        return view('purchases.show', compact('purchase'));
    }
    
    /**
     * Method untuk membatalkan (soft delete) transaksi.
     */
    public function cancel(Purchase $purchase)
    {
        $purchase->delete();

        return redirect()->route('purchases.index', ['status' => 'selesai'])->with('success', "Transaksi dengan kode {$purchase->purchase_code} berhasil dibatalkan.");
    }

    /**
     * Pulihkan transaksi yang di-soft-delete.
     */
    public function restore($id)
    {
        $purchase = Purchase::onlyTrashed()->findOrFail($id);
        $purchase->restore();

        return redirect()->route('purchases.index', ['status' => 'dibatalkan'])->with('success', "Transaksi dengan kode {$purchase->purchase_code} berhasil dipulihkan.");
    }
    
    /**
     * Menghapus sumber daya secara permanen.
     */
    public function destroy(Purchase $purchase)
    {
        $purchase->forceDelete();

        return redirect()->route('purchases.index')->with('success', "Transaksi dengan kode {$purchase->purchase_code} berhasil dihapus permanen.");
    }
}