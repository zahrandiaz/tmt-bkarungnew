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
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchases = Purchase::with('supplier')->latest()->paginate(10);
        return view('purchases.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $products = Product::orderBy('name', 'asc')->get();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePurchaseRequest $request)
    {
        $validatedData = $request->validated();

        try {
            DB::transaction(function () use ($validatedData) {
                $purchase = Purchase::create([
                    'supplier_id' => $validatedData['supplier_id'],
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

            return redirect()->route('purchases.index')->with('success', 'Transaksi pembelian berhasil disimpan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage())->withInput();
        }
    }


    /**
     * Display the specified resource.
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
        $purchase->delete(); // Ini akan menjalankan soft delete

        return redirect()->route('purchases.index')->with('success', "Transaksi #{$purchase->id} berhasil dibatalkan.");
    }
    
    /**
     * Remove the specified resource from storage.
     */
    // [MODIFIKASI] Implementasi Hard Delete
    public function destroy(Purchase $purchase)
    {
        // Gunakan forceDelete() untuk menghapus permanen
        $purchase->forceDelete();

        return redirect()->route('purchases.index')->with('success', "Transaksi #{$purchase->id} berhasil dihapus permanen.");
    }
}