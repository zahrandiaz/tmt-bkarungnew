<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\Expense;
use Carbon\Carbon;
use App\Exports\SalesExport;
use App\Exports\PurchasesExport;
use App\Exports\StockExport;
use App\Exports\DepositsExport;
use App\Exports\ProfitAndLossExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    private function getDateRange(Request $request)
    {
        if ($request->has('period')) {
            $period = $request->input('period');
            switch ($period) {
                case 'today':
                    return [Carbon::today(), Carbon::today()];
                case 'this_week':
                    return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
                case 'this_month':
                    return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
                case 'this_year':
                    return [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()];
                default:
                    return [null, null];
            }
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
            return [$startDate, $endDate];
        }
        
        return [Carbon::today(), Carbon::today()];
    }

    public function salesReport(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        $salesQuery = Sale::query()->with('customer');
        
        if ($startDate && $endDate) {
            $salesQuery->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        }

        $statsQuery = (clone $salesQuery);
        $totalTransactions = $statsQuery->count();
        $totalRevenue = $statsQuery->sum('total_amount');

        $totalCogs = SaleDetail::whereHas('sale', function ($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
            }
        })->sum(DB::raw('quantity * purchase_price'));
        
        $grossProfit = $totalRevenue - $totalCogs;

        $tableQuery = Sale::withTrashed()->with(['customer', 'details']);
        if ($startDate && $endDate) {
            $tableQuery->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        }
        $sales = $tableQuery->latest()->paginate(10)->appends($request->query());

        // [BARU V1.15.0] Hitung laba untuk setiap transaksi yang ditampilkan
        foreach ($sales as $sale) {
            if (!$sale->trashed()) { // Hanya hitung laba untuk transaksi yang tidak dibatalkan
                $totalHpp = $sale->details->sum(function ($detail) {
                    return $detail->quantity * $detail->purchase_price;
                });
                $sale->profit = $sale->total_amount - $totalHpp;
            } else {
                $sale->profit = 0;
            }
        }

        return view('reports.sales', [
            'sales' => $sales,
            'startDate' => $startDate ? $startDate->format('Y-m-d') : null,
            'endDate' => $endDate ? $endDate->format('Y-m-d') : null,
            'period' => $request->input('period', $request->has('start_date') ? null : 'today'),
            'totalTransactions' => $totalTransactions,
            'totalRevenue' => $totalRevenue,
            'totalCogs' => $totalCogs,
            'grossProfit' => $grossProfit,
        ]);
    }

    public function purchasesReport(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        $purchasesQuery = Purchase::query();

        if ($startDate && $endDate) {
            $purchasesQuery->whereBetween('purchase_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        }

        $statsQuery = (clone $purchasesQuery);
        $totalTransactions = $statsQuery->count();
        $totalExpenditure = $statsQuery->sum('total_amount');
        
        $tableQuery = Purchase::withTrashed()->with('supplier');
        if ($startDate && $endDate) {
            $tableQuery->whereBetween('purchase_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        }
        $purchases = $tableQuery->latest()->paginate(10)->appends($request->query());

        return view('reports.purchases', [
            'purchases' => $purchases,
            'startDate' => $startDate ? $startDate->format('Y-m-d') : null,
            'endDate' => $endDate ? $endDate->format('Y-m-d') : null,
            'period' => $request->input('period', $request->has('start_date') ? null : 'today'),
            'totalTransactions' => $totalTransactions,
            'totalExpenditure' => $totalExpenditure,
        ]);
    }

    public function stockReport()
    {
        $products = Product::with(['category', 'type'])->orderBy('name', 'asc')->paginate(15);
        return view('reports.stock', ['products' => $products]);
    }

    public function profitAndLossReport(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        $salesDetailsQuery = SaleDetail::query()
            ->whereHas('sale', function ($query) {
                $query->where('payment_status', 'Lunas');
            });

        $expensesQuery = Expense::query(); 

        if ($startDate && $endDate) {
            $startOfDay = $startDate->copy()->startOfDay();
            $endOfDay = $endDate->copy()->endOfDay();
            
            $salesDetailsQuery->whereHas('sale', function ($query) use ($startOfDay, $endOfDay) {
                $query->whereBetween('sale_date', [$startOfDay, $endOfDay]);
            });

            $expensesQuery->whereBetween('expense_date', [$startOfDay, $endOfDay]);
        }

        $reportData = $salesDetailsQuery
            ->select(
                DB::raw('SUM(quantity * sale_price) as total_revenue'),
                DB::raw('SUM(quantity * purchase_price) as total_cogs')
            )
            ->first();

        $totalRevenue = $reportData->total_revenue ?? 0;
        $totalCostOfGoods = $reportData->total_cogs ?? 0;

        $totalExpenses = $expensesQuery->sum('amount');
        
        $expensesByCategory = (clone $expensesQuery)->with('category')
            ->select('expense_category_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('expense_category_id')
            ->get();

        $grossProfit = $totalRevenue - $totalCostOfGoods;
        $netProfit = $grossProfit - $totalExpenses;

        return view('reports.profit_and_loss', [
            'startDate' => $startDate ? $startDate->format('Y-m-d') : null,
            'endDate' => $endDate ? $endDate->format('Y-m-d') : null,
            'period' => $request->input('period', $request->has('start_date') ? null : 'today'),
            'totalRevenue' => $totalRevenue,
            'totalCostOfGoods' => $totalCostOfGoods,
            'grossProfit' => $grossProfit,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'expensesByCategory' => $expensesByCategory,
        ]);
    }

    // [BARU] Method untuk Laporan Setoran
    public function depositReport(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        // Query dasar untuk penjualan yang tidak dibatalkan dalam rentang tanggal
        $salesQuery = Sale::query()
            ->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);

        // Kalkulasi total modal (HPP) untuk kartu statistik
        $totalDeposit = SaleDetail::whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        })->sum(DB::raw('quantity * purchase_price'));
        
        // Ambil data untuk tabel dengan paginasi
        $sales = (clone $salesQuery)->with('customer', 'details')
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        // Hitung total modal per transaksi untuk ditampilkan di tabel
        foreach ($sales as $sale) {
            $sale->total_modal = $sale->details->sum(function ($detail) {
                return $detail->quantity * $detail->purchase_price;
            });
        }

        return view('reports.deposits', [
            'sales' => $sales,
            'totalDeposit' => $totalDeposit,
            'startDate' => $startDate ? $startDate->format('Y-m-d') : null,
            'endDate' => $endDate ? $endDate->format('Y-m-d') : null,
            'period' => $request->input('period', $request->has('start_date') ? null : 'today'),
        ]);
    }
    
    // [BARU V1.15.0] Method untuk API detail penjualan
    public function getSaleDetails($id)
    {
        $sale = Sale::withTrashed()->with('details.product')->find($id);
        if (!$sale) {
            return response()->json(['error' => 'Transaksi tidak ditemukan'], 404);
        }
        return response()->json($sale->details);
    }

    // [BARU V1.15.0] Method untuk API detail pembelian
    public function getPurchaseDetails($id)
    {
        $purchase = Purchase::withTrashed()->with('details.product')->find($id);
        if (!$purchase) {
            return response()->json(['error' => 'Transaksi tidak ditemukan'], 404);
        }
        return response()->json($purchase->details);
    }

    // [GANTI NAMA] Ubah nama method dari exportSales menjadi exportSalesCsv
    public function exportSalesCsv(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $fileName = 'laporan-penjualan-' . Carbon::now()->format('Y-m-d') . '.csv';
        return Excel::download(new SalesExport($startDate, $endDate), $fileName);
    }

    // [BARU] Method untuk ekspor Laporan Penjualan (PDF)
    public function exportSalesPdf(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        $salesQuery = Sale::query()->with('customer');
        $salesQuery->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);

        $statsQuery = (clone $salesQuery);
        $totalTransactions = $statsQuery->count();
        $totalRevenue = $statsQuery->sum('total_amount');

        $totalCogs = SaleDetail::whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        })->sum(DB::raw('quantity * purchase_price'));
        
        $grossProfit = $totalRevenue - $totalCogs;

        $tableQuery = Sale::withTrashed()->with(['customer', 'details']);
        $tableQuery->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        $sales = $tableQuery->latest()->get(); // Ambil semua data tanpa paginasi

        foreach ($sales as $sale) {
            if (!$sale->trashed()) {
                $totalHpp = $sale->details->sum(fn($detail) => $detail->quantity * $detail->purchase_price);
                $sale->profit = $sale->total_amount - $totalHpp;
            } else {
                $sale->profit = 0;
            }
        }

        $pdf = Pdf::loadView('reports.pdf.sales', [
            'sales' => $sales,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'totalTransactions' => $totalTransactions,
            'totalRevenue' => $totalRevenue,
            'totalCogs' => $totalCogs,
            'grossProfit' => $grossProfit,
        ]);

        $fileName = 'laporan-penjualan-' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->stream($fileName);
    }

    // [GANTI NAMA] Ubah nama method dari exportPurchases menjadi exportPurchasesCsv
    public function exportPurchasesCsv(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $fileName = 'laporan-pembelian-' . Carbon::now()->format('Y-m-d') . '.csv';
        return Excel::download(new PurchasesExport($startDate, $endDate), $fileName);
    }

    // [BARU] Method untuk ekspor Laporan Pembelian (PDF)
    public function exportPurchasesPdf(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        $purchasesQuery = Purchase::query();
        $purchasesQuery->whereBetween('purchase_date', [$startDate->startOfDay(), $endDate->endOfDay()]);

        $statsQuery = (clone $purchasesQuery);
        $totalTransactions = $statsQuery->count();
        $totalExpenditure = $statsQuery->sum('total_amount');
        
        $tableQuery = Purchase::withTrashed()->with('supplier');
        $tableQuery->whereBetween('purchase_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        $purchases = $tableQuery->latest()->get(); // Ambil semua data tanpa paginasi

        $pdf = Pdf::loadView('reports.pdf.purchases', [
            'purchases' => $purchases,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'totalTransactions' => $totalTransactions,
            'totalExpenditure' => $totalExpenditure,
        ]);

        $fileName = 'laporan-pembelian-' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->stream($fileName);
    }

    public function exportStock()
    {
        $fileName = 'laporan-stok-' . Carbon::now()->format('Y-m-d') . '.csv';
        return Excel::download(new StockExport(), $fileName);
    }

    // [BARU] Method Ekspor Laporan Setoran (CSV)
    public function exportDepositsCsv(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $fileName = 'laporan-setoran-' . Carbon::now()->format('Y-m-d') . '.csv';
        
        return Excel::download(new DepositsExport($startDate, $endDate), $fileName);
    }

    // [BARU] Method Ekspor Laporan Setoran (PDF)
    public function exportDepositsPdf(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        $salesQuery = Sale::query()
            ->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);

        $totalDeposit = SaleDetail::whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        })->sum(DB::raw('quantity * purchase_price'));
        
        // Ambil SEMUA data untuk PDF (tanpa paginasi)
        $sales = (clone $salesQuery)->with('customer', 'details')->latest()->get();

        foreach ($sales as $sale) {
            $sale->total_modal = $sale->details->sum(function ($detail) {
                return $detail->quantity * $detail->purchase_price;
            });
        }

        $pdf = Pdf::loadView('reports.pdf.deposits', [
            'sales' => $sales,
            'totalDeposit' => $totalDeposit,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);

        $fileName = 'laporan-setoran-' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->stream($fileName);
    }

    // [BARU] Method Ekspor Laporan Laba Rugi (CSV)
    public function exportProfitAndLossCsv(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        // Mengambil data dengan logika yang sama seperti di profitAndLossReport
        $data = $this->getProfitAndLossData($startDate, $endDate);

        $fileName = 'laporan-laba-rugi-' . Carbon::now()->format('Y-m-d') . '.csv';
        return Excel::download(new ProfitAndLossExport($data), $fileName);
    }

    // [BARU] Method Ekspor Laporan Laba Rugi (PDF)
    public function exportProfitAndLossPdf(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $data = $this->getProfitAndLossData($startDate, $endDate);
        
        $pdf = Pdf::loadView('reports.pdf.profit_and_loss', $data);

        $fileName = 'laporan-laba-rugi-' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->stream($fileName);
    }

    // [BARU] Private helper method untuk menghindari duplikasi kode
    private function getProfitAndLossData(Carbon $startDate, Carbon $endDate): array
    {
        $salesDetailsQuery = SaleDetail::query()
            ->whereHas('sale', function ($query) {
                $query->where('payment_status', 'Lunas');
            });

        $expensesQuery = Expense::query(); 

        $startOfDay = $startDate->copy()->startOfDay();
        $endOfDay = $endDate->copy()->endOfDay();
        
        $salesDetailsQuery->whereHas('sale', function ($query) use ($startOfDay, $endOfDay) {
            $query->whereBetween('sale_date', [$startOfDay, $endOfDay]);
        });

        $expensesQuery->whereBetween('expense_date', [$startOfDay, $endOfDay]);

        $reportData = (clone $salesDetailsQuery)
            ->select(
                DB::raw('SUM(quantity * sale_price) as total_revenue'),
                DB::raw('SUM(quantity * purchase_price) as total_cogs')
            )
            ->first();

        $totalRevenue = $reportData->total_revenue ?? 0;
        $totalCostOfGoods = $reportData->total_cogs ?? 0;
        $totalExpenses = (clone $expensesQuery)->sum('amount');
        
        $expensesByCategory = (clone $expensesQuery)->with('category')
            ->select('expense_category_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('expense_category_id')
            ->get();

        $grossProfit = $totalRevenue - $totalCostOfGoods;
        $netProfit = $grossProfit - $totalExpenses;

        return [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'totalRevenue' => $totalRevenue,
            'totalCostOfGoods' => $totalCostOfGoods,
            'grossProfit' => $grossProfit,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'expensesByCategory' => $expensesByCategory,
        ];
    }
}