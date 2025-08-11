<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Http\Requests\StoreSaleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; // <-- [BARU] Tambahkan ini

class SaleController extends Controller
{
    // ... (method index, create, store tetap sama) ...
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = Sale::query();
        if ($status == 'dibatalkan') {
            $query->onlyTrashed();
        } elseif ($status == 'semua') {
            $query->withTrashed();
        }
        $sales = $query->with('customer')->latest()->paginate(10);
        return view('sales.index', compact('sales'));
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
                // [LOGIKA BARU] Tentukan jumlah yang dibayar berdasarkan status
                $totalAmount = $validatedData['total_amount'];
                $paymentStatus = $validatedData['payment_status'];
                $totalPaid = 0;

                if ($paymentStatus === 'lunas') {
                    $totalPaid = $totalAmount;
                } elseif ($paymentStatus === 'belum lunas') {
                    // Pastikan down_payment tidak null, jika null anggap 0
                    $totalPaid = $validatedData['down_payment'] ?? 0;
                }

                // Generate Invoice Number
                $latestSaleId = Sale::withTrashed()->latest('id')->first()?->id ?? 0;
                $invoiceNumber = 'INV/' . now()->format('Ym') . '/' . str_pad($latestSaleId + 1, 5, '0', STR_PAD_LEFT);

                // Buat entri penjualan dengan data pembayaran
                $sale = Sale::create([
                    'invoice_number' => $invoiceNumber,
                    'customer_id' => $validatedData['customer_id'],
                    'sale_date' => $validatedData['sale_date'],
                    'total_amount' => $totalAmount,
                    'notes' => $validatedData['notes'] ?? null,
                    'user_id' => $request->user()->id,
                    // [KOLOM BARU]
                    'payment_method' => $validatedData['payment_method'],
                    'payment_status' => $paymentStatus,
                    'down_payment' => $validatedData['down_payment'] ?? null,
                    'total_paid' => $totalPaid,
                ]);

                // Simpan detail item penjualan
                foreach ($validatedData['items'] as $item) {
                    $sale->details()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'sale_price' => $item['sale_price'],
                    ]);

                    // Kurangi stok produk
                    $product = Product::find($item['product_id']);
                    if ($product) {
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
     * Display the specified resource.
     */
    public function show($id)
    {
        // [MODIFIKASI] Gunakan withTrashed() dan muat relasi user
        $sale = Sale::withTrashed()->with(['customer', 'user', 'details.product'])->findOrFail($id);
        return view('sales.show', compact('sale'));
    }
    
    // ... (method cancel, restore, destroy tetap sama) ...
    public function cancel(Sale $sale)
    {
        $sale->delete(); 
        return redirect()->route('sales.index', ['status' => 'selesai'])->with('success', "Transaksi dengan invoice {$sale->invoice_number} berhasil dibatalkan.");
    }

    public function restore($id)
    {
        $sale = Sale::onlyTrashed()->findOrFail($id);
        $sale->restore();
        return redirect()->route('sales.index', ['status' => 'dibatalkan'])->with('success', "Transaksi dengan invoice {$sale->invoice_number} berhasil dipulihkan.");
    }
    
    public function destroy($id)
    {
        $sale = Sale::withTrashed()->findOrFail($id);
        $sale->forceDelete();
        return redirect()->route('sales.index')->with('success', "Transaksi dengan invoice {$sale->invoice_number} berhasil dihapus permanen.");
    }

    /**
     * [BARU] Menampilkan view untuk cetak struk thermal.
     */
    public function printThermal($id)
    {
        $sale = Sale::withTrashed()->with(['customer', 'user', 'details.product'])->findOrFail($id);
        return view('sales.print-thermal', compact('sale'));
    }

    /**
     * [BARU] Mengunduh transaksi dalam format PDF.
     */
    public function downloadPDF($id)
    {
        $sale = Sale::withTrashed()->with(['customer', 'user', 'details.product'])->findOrFail($id);
        $pdf = Pdf::loadView('sales.print-pdf', compact('sale'));
        
        // Nama file: INV-202508-00001.pdf
        $fileName = str_replace('/', '-', $sale->invoice_number) . '.pdf';

        return $pdf->download($fileName);
    }
}