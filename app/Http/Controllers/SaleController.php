<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Http\Requests\StoreSaleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        
        $query = Sale::query();

        if ($status == 'dibatalkan') {
            $query->onlyTrashed();
        } elseif ($status == 'semua') {
            $query->withTrashed();
        } else {
            // Kasus default (jika status adalah 'selesai' atau null) akan menampilkan yang tidak di-soft-delete
        }

        $sales = $query->with('customer')->latest()->paginate(10);
        
        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::orderBy('name', 'asc')->get();

        $products = Product::orderBy('name', 'asc')->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'selling_price' => $product->selling_price,
            ];
        });
        
        return view('sales.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleRequest $request)
    {
        $validatedData = $request->validated();

        try {
            DB::transaction(function () use ($validatedData, $request) { 
                // [MODIFIKASI] Format Invoice Lebih Baik
                $latestSaleId = Sale::withTrashed()->latest('id')->first()?->id ?? 0;
                $invoiceNumber = 'INV/' . now()->format('Ym') . '/' . str_pad($latestSaleId + 1, 5, '0', STR_PAD_LEFT);

                $sale = Sale::create([
                    'invoice_number' => $invoiceNumber, 
                    'customer_id' => $validatedData['customer_id'],
                    'user_id' => $request->user()->id, 
                    'sale_date' => $validatedData['sale_date'],
                    'total_amount' => $validatedData['total_amount'],
                    'notes' => $validatedData['notes'],
                ]);

                foreach ($validatedData['items'] as $item) {
                    $sale->details()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'sale_price' => $item['sale_price'],
                    ]);
                }
            });

            return redirect()->route('sales.index', ['status' => 'selesai'])->with('success', 'Transaksi penjualan berhasil disimpan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage())->withInput();
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load('customer', 'details.product');
        return view('sales.show', compact('sale'));
    }
    
    /**
     * Method untuk membatalkan (soft delete) transaksi.
     */
    public function cancel(Sale $sale)
    {
        $sale->delete(); 
        
        return redirect()->route('sales.index', ['status' => 'selesai'])->with('success', "Transaksi dengan invoice {$sale->invoice_number} berhasil dibatalkan.");
    }

    /**
     * Pulihkan transaksi yang di-soft-delete.
     */
    public function restore($id)
    {
        $sale = Sale::onlyTrashed()->findOrFail($id);
        $sale->restore();

        return redirect()->route('sales.index', ['status' => 'dibatalkan'])->with('success', "Transaksi dengan invoice {$sale->invoice_number} berhasil dipulihkan.");
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        $sale->forceDelete();

        return redirect()->route('sales.index')->with('success', "Transaksi dengan invoice {$sale->invoice_number} berhasil dihapus permanen.");
    }
}