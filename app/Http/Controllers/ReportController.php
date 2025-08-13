<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\SaleDetail;
use App\Models\Expense; // <-- [1. TAMBAHKAN INI]
use Carbon\Carbon;
use App\Exports\SalesExport;
use App\Exports\PurchasesExport;
use App\Exports\StockExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    // ... (method salesReport, purchasesReport, stockReport tetap sama) ...
    public function salesReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $salesQuery = Sale::withTrashed()->with('customer');
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $salesQuery->whereBetween('created_at', [$start, $end]);
        }
        $sales = $salesQuery->latest()->paginate(10)->appends($request->query());
        return view('reports.sales', [
            'sales' => $sales,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function purchasesReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $purchasesQuery = Purchase::withTrashed()->with('supplier');
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $purchasesQuery->whereBetween('created_at', [$start, $end]);
        }
        $purchases = $purchasesQuery->latest()->paginate(10)->appends($request->query());
        return view('reports.purchases', [
            'purchases' => $purchases,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function stockReport()
    {
        $products = Product::with(['category', 'type'])
            ->orderBy('name', 'asc')
            ->paginate(15);
        return view('reports.stock', [
            'products' => $products,
        ]);
    }

    /**
     * [DIROMBAK] Menampilkan laporan laba rugi lengkap.
     */
    public function profitAndLossReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Inisialisasi query builder
        $salesQuery = Sale::query()->where('payment_status', 'lunas'); // Hanya hitung yang lunas
        $expensesQuery = Expense::query();

        // Terapkan filter tanggal jika ada
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $salesQuery->whereBetween('sale_date', [$start, $end]);
            $expensesQuery->whereBetween('expense_date', [$start, $end]);
        }

        // Hitung Total Pendapatan
        // Note: Ini masih menggunakan pendekatan HPP Sederhana. Nanti bisa kita tingkatkan.
        $totalRevenue = 0;
        $totalCostOfGoods = 0;
        foreach ($salesQuery->with('details.product')->get() as $sale) {
            foreach ($sale->details as $detail) {
                $totalRevenue += ($detail->quantity * $detail->sale_price);
                if ($detail->product) {
                    $totalCostOfGoods += ($detail->quantity * $detail->product->purchase_price);
                }
            }
        }

        // Hitung Total Biaya Operasional
        $totalExpenses = $expensesQuery->sum('amount');
        
        // Kalkulasi Final
        $grossProfit = $totalRevenue - $totalCostOfGoods;
        $netProfit = $grossProfit - $totalExpenses;

        return view('reports.profit_and_loss', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalRevenue' => $totalRevenue,
            'totalCostOfGoods' => $totalCostOfGoods,
            'grossProfit' => $grossProfit,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
        ]);
    }

    // ... (method export tetap sama) ...
    public function exportSales(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $fileName = 'laporan-penjualan-' . Carbon::now()->format('Y-m-d') . '.csv';

        return Excel::download(new SalesExport($startDate, $endDate), $fileName);
    }

    public function exportPurchases(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $fileName = 'laporan-pembelian-' . Carbon::now()->format('Y-m-d') . '.csv';

        return Excel::download(new PurchasesExport($startDate, $endDate), $fileName);
    }

    public function exportStock()
    {
        $fileName = 'laporan-stok-' . Carbon::now()->format('Y-m-d') . '.csv';
        return Excel::download(new StockExport(), $fileName);
    }
}