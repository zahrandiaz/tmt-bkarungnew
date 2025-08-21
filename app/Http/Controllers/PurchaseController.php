<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturnDetail;
use App\Models\Supplier;
use App\Http\Requests\StorePurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use App\Models\Setting;

class PurchaseController extends Controller
{
    /**
     * [MODIFIKASI] Eager load relasi 'returns'.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Purchase::query();

        if ($status == 'dibatalkan') {
            $query->onlyTrashed();
        } elseif ($status == 'semua') {
            $query->withTrashed();
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('purchase_code', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($subQuery) use ($search) {
                      $subQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // [PERBAIKAN] Tambahkan 'returns' ke dalam with()
        $purchases = $query->with('supplier', 'returns')->latest()->paginate(10)->appends($request->query());
        
        return view('purchases.index', compact('purchases', 'search'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        return view('purchases.create', compact('suppliers'));
    }

    public function store(StorePurchaseRequest $request)
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

                $latestPurchaseId = Purchase::withTrashed()->latest('id')->first()?->id ?? 0;
                $purchaseCode = 'PUR/' . now()->format('Ym') . '/' . str_pad($latestPurchaseId + 1, 5, '0', STR_PAD_LEFT);
                
                $invoiceImagePath = null;
                if ($request->hasFile('invoice_image')) {
                    $image = $request->file('invoice_image');
                    $fileName = time() . '_' . \Illuminate\Support\Str::random(10) . '.webp';
                    $imageCompressed = Image::read($image->getRealPath())->toWebp(75);
                    Storage::disk('public')->put('invoices/' . $fileName, (string) $imageCompressed);
                    $invoiceImagePath = 'invoices/' . $fileName;
                }

                $purchase = Purchase::create([
                    'purchase_code' => $purchaseCode,
                    'supplier_id' => $validatedData['supplier_id'],
                    'purchase_date' => $validatedData['purchase_date'],
                    'total_amount' => $totalAmount,
                    'reference_number' => $validatedData['reference_number'] ?? null,
                    'notes' => $validatedData['notes'] ?? null,
                    'invoice_image_path' => $invoiceImagePath,
                    'user_id' => $request->user()->id,
                    'payment_method' => $validatedData['payment_method'],
                    'payment_status' => $paymentStatus,
                    'down_payment' => $validatedData['down_payment'] ?? null,
                    'total_paid' => $totalPaid,
                ]);

                $isStockEnabled = Setting::where('key', 'enable_automatic_stock')->first()->value ?? '0';

                foreach ($validatedData['items'] as $item) {
                    $purchase->details()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'purchase_price' => $item['purchase_price'],
                    ]);

                    $product = Product::find($item['product_id']);
                    if ($product) {
                        if ($isStockEnabled === '1') {
                            $product->increment('stock', $item['quantity']);
                        }
                    }
                }
            });

            return redirect()->route('purchases.index', ['status' => 'selesai'])->with('success', 'Transaksi pembelian berhasil disimpan.');

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
        $purchase = Purchase::withTrashed()->with(['supplier', 'user', 'details.product', 'returns', 'returns.details.product'])->findOrFail($id);
        return view('purchases.show', compact('purchase'));
    }
    
    public function cancel(Purchase $purchase)
    {
        $purchase->delete();
        return redirect()->route('purchases.index', ['status' => 'selesai'])->with('success', "Transaksi dengan kode {$purchase->purchase_code} berhasil dibatalkan.");
    }

    public function restore($id)
    {
        $purchase = Purchase::onlyTrashed()->with('details.product')->findOrFail($id);
        
        $isStockEnabled = Setting::where('key', 'enable_automatic_stock')->first()->value ?? '0';
        if ($isStockEnabled === '1') {
            foreach ($purchase->details as $detail) {
                if ($detail->product) {
                    $detail->product->decrement('stock', $detail->quantity);
                }
            }
        }
        
        $purchase->restore();
        return redirect()->route('purchases.index', ['status' => 'dibatalkan'])->with('success', "Transaksi dengan kode {$purchase->purchase_code} berhasil dipulihkan.");
    }
    
    public function destroy($id)
    {
        $purchase = Purchase::withTrashed()->findOrFail($id);
        
        if ($purchase->invoice_image_path) {
            Storage::disk('public')->delete($purchase->invoice_image_path);
        }
        
        $purchase->forceDelete();
        return redirect()->route('purchases.index')->with('success', "Transaksi dengan kode {$purchase->purchase_code} berhasil dihapus permanen.");
    }

    public function getPurchaseDetailsForReturn(Purchase $purchase)
    {
        $purchase->load('supplier', 'details.product');

        $purchase->details->each(function ($detail) use ($purchase) {
            $totalReturned = PurchaseReturnDetail::join('purchase_returns', 'purchase_return_details.purchase_return_id', '=', 'purchase_returns.id')
                ->where('purchase_returns.purchase_id', $purchase->id)
                ->where('purchase_return_details.product_id', $detail->product_id)
                ->sum('purchase_return_details.quantity');
                
            $detail->returnable_quantity = $detail->quantity - $totalReturned;
        });

        return response()->json($purchase);
    }
    
    public function search(Request $request)
    {
        $query = $request->input('q');

        $purchases = Purchase::with('supplier')
            ->where('purchase_code', 'like', "%{$query}%")
            ->orWhereHas('supplier', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->whereNull('deleted_at')
            ->limit(10)
            ->get();

        return response()->json($purchases->map(function ($purchase) {
            return [
                'id' => $purchase->id,
                'text' => $purchase->purchase_code . ' - ' . $purchase->supplier->name,
            ];
        }));
    }
}