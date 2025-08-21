<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturnDetail;
use App\Models\PurchaseDetail;
use App\Http\Requests\StoreSaleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Setting;

class SaleController extends Controller
{
    /**
     * [MODIFIKASI] Eager load relasi 'returns'.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');
        
        $query = Sale::query();

        if ($status == 'dibatalkan') {
            $query->onlyTrashed();
        } elseif ($status == 'semua') {
            $query->withTrashed();
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($subQuery) use ($search) {
                      $subQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // [PERBAIKAN] Tambahkan 'returns' ke dalam with()
        $sales = $query->with('customer', 'returns')->latest()->paginate(10)->appends($request->query());
        
        return view('sales.index', compact('sales', 'search'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name', 'asc')->get();
        return view('sales.create', compact('customers'));
    }

    public function store(StoreSaleRequest $request)
    {
        $validatedData = $request->validated();

        try {
            DB::transaction(function () use ($validatedData, $request) {
                $totalAmount = $validatedData['total_amount'];
                $paymentStatusRaw = $validatedData['payment_status'];
                $paymentStatus = ucwords(str_replace('_', ' ', $paymentStatusRaw));
                $totalPaid = 0;
                if ($paymentStatus === 'Lunas') {
                    $totalPaid = $totalAmount;
                } elseif ($paymentStatus === 'Belum Lunas') {
                    $totalPaid = $validatedData['down_payment'] ?? 0;
                }

                $latestSaleId = Sale::withTrashed()->latest('id')->first()?->id ?? 0;
                $invoiceNumber = 'INV/' . now()->format('Ym') . '/' . str_pad($latestSaleId + 1, 5, '0', STR_PAD_LEFT);

                $sale = Sale::create([
                    'invoice_number' => $invoiceNumber,
                    'customer_id' => $validatedData['customer_id'],
                    'sale_date' => $validatedData['sale_date'],
                    'total_amount' => $totalAmount,
                    'notes' => $validatedData['notes'] ?? null,
                    'user_id' => $request->user()->id,
                    'payment_method' => $validatedData['payment_method'],
                    'payment_status' => $paymentStatus,
                    'down_payment' => $validatedData['down_payment'] ?? null,
                    'total_paid' => $totalPaid,
                ]);

                $isStockEnabled = Setting::where('key', 'enable_automatic_stock')->first()->value ?? '0';

                foreach ($validatedData['items'] as $item) {
                    $product = Product::find($item['product_id']);
                    if (!$product) continue;

                    $lastPurchaseDetail = PurchaseDetail::where('product_id', $product->id)
                        ->whereHas('purchase', function ($query) use ($sale) {
                            $query->where('purchase_date', '<=', $sale->sale_date);
                        })
                        ->latest('created_at')
                        ->first();

                    $hppToRecord = $lastPurchaseDetail ? $lastPurchaseDetail->purchase_price : $product->purchase_price;

                    $sale->details()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'sale_price' => $item['sale_price'],
                        'purchase_price' => $hppToRecord,
                    ]);

                    if ($isStockEnabled === '1') {
                        $product->decrement('stock', $item['quantity']);
                    }
                }
            });

            return redirect()->route('sales.index', ['status' => 'selesai'])->with('success', 'Transaksi penjualan berhasil disimpan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * [MODIFIKASI] Eager load relasi 'returns'.
     */
    public function show($id)
    {
        // [PERBAIKAN] Tambahkan 'returns' dan 'returns.details' ke dalam with()
        $sale = Sale::withTrashed()->with(['customer', 'user', 'details.product', 'returns', 'returns.details.product'])->findOrFail($id);
        return view('sales.show', compact('sale'));
    }
    
    public function cancel(Sale $sale)
    {
        $sale->delete(); 
        return redirect()->route('sales.index', ['status' => 'selesai'])->with('success', "Transaksi dengan invoice {$sale->invoice_number} berhasil dibatalkan.");
    }

    public function restore($id)
    {
        $sale = Sale::onlyTrashed()->with('details.product')->findOrFail($id);

        $isStockEnabled = Setting::where('key', 'enable_automatic_stock')->first()->value ?? '0';
        if ($isStockEnabled === '1') {
            foreach ($sale->details as $detail) {
                if ($detail->product) {
                    $detail->product->increment('stock', $detail->quantity);
                }
            }
        }

        $sale->restore();
        return redirect()->route('sales.index', ['status' => 'dibatalkan'])->with('success', "Transaksi dengan invoice {$sale->invoice_number} berhasil dipulihkan.");
    }
    
    public function destroy($id)
    {
        $sale = Sale::withTrashed()->findOrFail($id);
        $sale->forceDelete();
        return redirect()->route('sales.index')->with('success', "Transaksi dengan invoice {$sale->invoice_number} berhasil dihapus permanen.");
    }

    public function printThermal($id)
    {
        $sale = Sale::withTrashed()->with(['customer', 'user', 'details.product'])->findOrFail($id);
        return view('sales.print-thermal', compact('sale'));
    }

    public function downloadPDF($id)
    {
        $sale = Sale::withTrashed()->with(['customer', 'user', 'details.product'])->findOrFail($id);
        $pdf = Pdf::loadView('sales.print-pdf', compact('sale'));
        
        $fileName = str_replace('/', '-', $sale->invoice_number) . '.pdf';

        return $pdf->download($fileName);
    }

    public function getSaleDetailsForReturn(Sale $sale)
    {
        $sale->load('customer', 'details.product');

        $sale->details->each(function ($detail) use ($sale) {
            $totalReturned = SaleReturnDetail::join('sale_returns', 'sale_return_details.sale_return_id', '=', 'sale_returns.id')
                ->where('sale_returns.sale_id', $sale->id)
                ->where('sale_return_details.product_id', $detail->product_id)
                ->sum('sale_return_details.quantity');

            $detail->returnable_quantity = $detail->quantity - $totalReturned;
        });

        return response()->json($sale);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        $sales = Sale::with('customer')
            ->where('invoice_number', 'like', "%{$query}%")
            ->orWhereHas('customer', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->whereNull('deleted_at')
            ->limit(10)
            ->get();

        return response()->json($sales->map(function ($sale) {
            return [
                'id' => $sale->id,
                'text' => $sale->invoice_number . ' - ' . $sale->customer->name,
            ];
        }));
    }
}