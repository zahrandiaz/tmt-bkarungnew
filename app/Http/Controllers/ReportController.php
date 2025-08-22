<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use App\Exports\SalesExport;
use App\Exports\PurchasesExport;
use App\Exports\StockExport;
use App\Exports\DepositsExport;
use App\Exports\ProfitAndLossExport;
use App\Exports\CashFlowExport; // [BARU]
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

    // ... (metode salesReport, purchasesReport, stockReport, profitAndLossReport, depositReport tetap sama) ...
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

        foreach ($sales as $sale) {
            if (!$sale->trashed()) { 
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

    public function depositReport(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        $salesQuery = Sale::query()
            ->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);

        $totalDeposit = SaleDetail::whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        })->sum(DB::raw('quantity * purchase_price'));
        
        $sales = (clone $salesQuery)->with('customer', 'details')
            ->latest()
            ->paginate(10)
            ->appends($request->query());

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

    // [REFAKTOR] Pindahkan logika ke private method agar bisa dipakai ulang
    private function getCashFlowData(Carbon $startDate, Carbon $endDate): array
    {
        $startOfDay = $startDate->copy()->startOfDay();
        $endOfDay = $endDate->copy()->endOfDay();

        // Data Arus Kas
        $cashInflowsQuery = Payment::query()->where('payable_type', Sale::class)->whereBetween('payment_date', [$startOfDay, $endOfDay]);
        $purchaseOutflowsQuery = Payment::query()->where('payable_type', Purchase::class)->whereBetween('payment_date', [$startOfDay, $endOfDay]);
        $expenseOutflowsQuery = Expense::query()->whereBetween('expense_date', [$startOfDay, $endOfDay]);

        // Data Piutang & Utang
        $receivablesQuery = Sale::query()->where('payment_status', 'Belum Lunas')->whereBetween('sale_date', [$startOfDay, $endOfDay]);
        $payablesQuery = Purchase::query()->where('payment_status', 'Belum Lunas')->whereBetween('purchase_date', [$startOfDay, $endOfDay]);

        // Kalkulasi Total
        $totalInflow = (clone $cashInflowsQuery)->sum('amount');
        $totalPurchaseOutflow = (clone $purchaseOutflowsQuery)->sum('amount');
        $totalExpenseOutflow = (clone $expenseOutflowsQuery)->sum('amount');
        $totalOutflow = $totalPurchaseOutflow + $totalExpenseOutflow;
        $netCashFlow = $totalInflow - $totalOutflow;
        $totalReceivables = (clone $receivablesQuery)->sum(DB::raw('total_amount - total_paid'));
        $totalPayables = (clone $payablesQuery)->sum(DB::raw('total_amount - total_paid'));

        return [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'totalInflow' => $totalInflow,
            'totalOutflow' => $totalOutflow,
            'netCashFlow' => $netCashFlow,
            'inflows' => (clone $cashInflowsQuery)->with('payable.customer')->latest('payment_date')->get(),
            'purchaseOutflows' => (clone $purchaseOutflowsQuery)->with('payable.supplier')->latest('payment_date')->get(),
            'expenseOutflows' => (clone $expenseOutflowsQuery)->with('category')->latest('expense_date')->get(),
            'totalReceivables' => $totalReceivables,
            'totalPayables' => $totalPayables,
            'receivables' => (clone $receivablesQuery)->with('customer')->latest('sale_date')->get(),
            'payables' => (clone $payablesQuery)->with('supplier')->latest('purchase_date')->get(),
        ];
    }

    public function cashFlowReport(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        // [BARU] Cek jika ada permintaan ekspor
        if ($request->has('export')) {
            if ($request->input('export') === 'csv') {
                return $this->exportCashFlowCsv($startDate, $endDate);
            }
            if ($request->input('export') === 'pdf') {
                return $this->exportCashFlowPdf($startDate, $endDate);
            }
        }

        $data = $this->getCashFlowData($startDate, $endDate);
        $data['period'] = $request->input('period', $request->has('start_date') ? null : 'today');

        return view('reports.cash_flow', $data);
    }

    // [BARU] Method Ekspor Arus Kas (CSV)
    private function exportCashFlowCsv(Carbon $startDate, Carbon $endDate)
    {
        $data = $this->getCashFlowData($startDate, $endDate);
        $fileName = 'laporan-arus-kas-' . Carbon::now()->format('Y-m-d') . '.csv';
        return Excel::download(new CashFlowExport($data), $fileName);
    }

    // [BARU] Method Ekspor Arus Kas (PDF)
    private function exportCashFlowPdf(Carbon $startDate, Carbon $endDate)
    {
        $data = $this->getCashFlowData($startDate, $endDate);
        $pdf = Pdf::loadView('reports.pdf.cash_flow', $data);
        $fileName = 'laporan-arus-kas-' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->stream($fileName);
    }
    
    // ... (sisa metode getSaleDetails, getPurchaseDetails, dan metode ekspor lainnya tetap sama) ...
    public function getSaleDetails($id)
    {
        $sale = Sale::withTrashed()->with('details.product')->find($id);
        if (!$sale) {
            return response()->json(['error' => 'Transaksi tidak ditemukan'], 404);
        }
        return response()->json($sale->details);
    }

    public function getPurchaseDetails($id)
    {
        $purchase = Purchase::withTrashed()->with('details.product')->find($id);
        if (!$purchase) {
            return response()->json(['error' => 'Transaksi tidak ditemukan'], 404);
        }
        return response()->json($purchase->details);
    }

    public function exportSalesCsv(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $fileName = 'laporan-penjualan-' . Carbon::now()->format('Y-m-d') . '.csv';
        return Excel::download(new SalesExport($startDate, $endDate), $fileName);
    }

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
        $sales = $tableQuery->latest()->get();

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

    public function exportPurchasesCsv(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $fileName = 'laporan-pembelian-' . Carbon::now()->format('Y-m-d') . '.csv';
        return Excel::download(new PurchasesExport($startDate, $endDate), $fileName);
    }

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
        $purchases = $tableQuery->latest()->get();

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

    public function exportDepositsCsv(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $fileName = 'laporan-setoran-' . Carbon::now()->format('Y-m-d') . '.csv';
        
        return Excel::download(new DepositsExport($startDate, $endDate), $fileName);
    }

    public function exportDepositsPdf(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        $salesQuery = Sale::query()
            ->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);

        $totalDeposit = SaleDetail::whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        })->sum(DB::raw('quantity * purchase_price'));
        
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

    public function exportProfitAndLossCsv(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        $data = $this->getProfitAndLossData($startDate, $endDate);

        $fileName = 'laporan-laba-rugi-' . Carbon::now()->format('Y-m-d') . '.csv';
        return Excel::download(new ProfitAndLossExport($data), $fileName);
    }

    public function exportProfitAndLossPdf(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $data = $this->getProfitAndLossData($startDate, $endDate);
        
        $pdf = Pdf::loadView('reports.pdf.profit_and_loss', $data);

        $fileName = 'laporan-laba-rugi-' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->stream($fileName);
    }

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