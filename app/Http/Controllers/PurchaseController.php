<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Http\Requests\StorePurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class PurchaseController extends Controller
{
    // ... (method index, create, store tetap sama) ...
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = Purchase::query();
        if ($status == 'dibatalkan') {
            $query->onlyTrashed();
        } elseif ($status == 'semua') {
            $query->withTrashed();
        }
        $purchases = $query->with('supplier')->latest()->paginate(10);
        return view('purchases.index', compact('purchases'));
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
                // [PERBAIKAN V1.9.0] Standarisasi nilai payment_status
                $totalAmount = $validatedData['total_amount'];
                $paymentStatusRaw = $validatedData['payment_status'];
                // Mengubah 'lunas' -> 'Lunas' atau 'belum lunas' -> 'Belum Lunas'
                $paymentStatus = ucwords(str_replace('_', ' ', $paymentStatusRaw));

                $totalPaid = 0;

                // Gunakan nilai yang sudah distandarisasi untuk logika dan penyimpanan
                if ($paymentStatus === 'Lunas') {
                    $totalPaid = $totalAmount;
                } elseif ($paymentStatus === 'Belum Lunas') {
                    $totalPaid = $validatedData['down_payment'] ?? 0;
                }

                // Generate Purchase Code
                $latestPurchaseId = Purchase::withTrashed()->latest('id')->first()?->id ?? 0;
                $purchaseCode = 'PUR/' . now()->format('Ym') . '/' . str_pad($latestPurchaseId + 1, 5, '0', STR_PAD_LEFT);
                
                // Handle file upload
                $invoiceImagePath = null;
                if ($request->hasFile('invoice_image')) {
                    $image = $request->file('invoice_image');
                    $fileName = time() . '_' . \Illuminate\Support\Str::random(10) . '.webp';
                    $imageCompressed = Image::read($image->getRealPath())->toWebp(75);
                    Storage::disk('public')->put('invoices/' . $fileName, (string) $imageCompressed);
                    $invoiceImagePath = 'invoices/' . $fileName;
                }

                // Buat entri pembelian dengan data yang sudah standar
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
                    'payment_status' => $paymentStatus, // Menyimpan nilai standar ('Lunas' / 'Belum Lunas')
                    'down_payment' => $validatedData['down_payment'] ?? null,
                    'total_paid' => $totalPaid,
                ]);

                // Simpan detail item dan tambah stok produk
                foreach ($validatedData['items'] as $item) {
                    $purchase->details()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'purchase_price' => $item['purchase_price'],
                    ]);

                    // [LOGIKA PENTING] Tambah stok produk
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->increment('stock', $item['quantity']);
                    }
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
    public function show($id)
    {
        // [MODIFIKASI] Gunakan withTrashed() dan muat relasi user
        $purchase = Purchase::withTrashed()->with(['supplier', 'user', 'details.product'])->findOrFail($id);
        return view('purchases.show', compact('purchase'));
    }
    
    // ... (method cancel, restore tetap sama) ...
    public function cancel(Purchase $purchase)
    {
        $purchase->delete();
        return redirect()->route('purchases.index', ['status' => 'selesai'])->with('success', "Transaksi dengan kode {$purchase->purchase_code} berhasil dibatalkan.");
    }

    public function restore($id)
    {
        $purchase = Purchase::onlyTrashed()->findOrFail($id);
        $purchase->restore();
        return redirect()->route('purchases.index', ['status' => 'dibatalkan'])->with('success', "Transaksi dengan kode {$purchase->purchase_code} berhasil dipulihkan.");
    }
    
    /**
     * Menghapus sumber daya secara permanen.
     */
    public function destroy($id)
    {
        // [MODIFIKASI] Gunakan findOrFail dengan withTrashed
        $purchase = Purchase::withTrashed()->findOrFail($id);
        
        if ($purchase->invoice_image_path) {
            Storage::disk('public')->delete($purchase->invoice_image_path);
        }
        
        $purchase->forceDelete();
        return redirect()->route('purchases.index')->with('success', "Transaksi dengan kode {$purchase->purchase_code} berhasil dihapus permanen.");
    }
}