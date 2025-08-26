<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use App\Exports\SalesExport;
use App\Exports\PurchasesExport;
use App\Exports\StockExport;
use App\Exports\DepositsExport;
use App\Exports\ProfitAndLossExport;
use App\Exports\CashFlowExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    //================================================================================
    // HELPER METHODS (PRIVATE)
    //================================================================================

    private function getDateRange(Request $request): array
    {
        if ($request->has('period')) {
            $period = $request->input('period');
            switch ($period) {
                case 'today':
                    return [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()];
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
            return [
                Carbon::parse($request->input('start_date'))->startOfDay(),
                Carbon::parse($request->input('end_date'))->endOfDay()
            ];
        }
        
        return [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()];
    }

    private function getSalesData(Carbon $startDate, Carbon $endDate): array
    {
        $salesQuery = Sale::whereNull('deleted_at')
            ->whereBetween('sale_date', [$startDate, $endDate]);

        $stats = (clone $salesQuery)
            ->selectRaw('COUNT(*) as total_transactions, SUM(total_amount) as total_revenue')
            ->first();

        $totalCogs = SaleDetail::whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->whereNull('deleted_at')->whereBetween('sale_date', [$startDate, $endDate]);
        })->sum(DB::raw('quantity * purchase_price'));

        return [
            'totalTransactions' => $stats->total_transactions ?? 0,
            'totalRevenue' => $stats->total_revenue ?? 0,
            'totalCogs' => $totalCogs,
            'grossProfit' => ($stats->total_revenue ?? 0) - $totalCogs,
        ];
    }

    private function getPurchasesData(Carbon $startDate, Carbon $endDate): array
    {
        $purchasesQuery = Purchase::whereNull('deleted_at')
            ->whereBetween('purchase_date', [$startDate, $endDate]);
            
        $stats = (clone $purchasesQuery)
            ->selectRaw('COUNT(*) as total_transactions, SUM(total_amount) as total_expenditure')
            ->first();

        return [
            'totalTransactions' => $stats->total_transactions ?? 0,
            'totalExpenditure' => $stats->total_expenditure ?? 0,
        ];
    }

    //================================================================================
    // WEB REPORT METHODS
    //================================================================================

    public function salesReport(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $salesData = $this->getSalesData($startDate, $endDate);

        $tableQuery = Sale::withTrashed()->with('customer')
            ->withSum(['details as total_modal' => function ($query) {
                $query->select(DB::raw('SUM(quantity * purchase_price)'));
            }], 'total_modal')
            ->whereBetween('sale_date', [$startDate, $endDate]);
            
        $sales = $tableQuery->latest()->paginate(10)->appends($request->query());

        return view('reports.sales', [
            'sales' => $sales,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'period' => $request->input('period', $request->has('start_date') ? null : 'today'),
        ] + $salesData);
    }

    public function purchasesReport(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $purchasesData = $this->getPurchasesData($startDate, $endDate);

        $tableQuery = Purchase::withTrashed()->with('supplier')
            ->whereBetween('purchase_date', [$startDate, $endDate]);
            
        $purchases = $tableQuery->latest()->paginate(10)->appends($request->query());

        return view('reports.purchases', [
            'purchases' => $purchases,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'period' => $request->input('period', $request->has('start_date') ? null : 'today'),
        ] + $purchasesData);
    }

    public function stockReport(Request $request)
    {
        $productsQuery = Product::with(['category', 'type']);
        if ($request->filled('search')) {
            $search = $request->input('search');
            $productsQuery->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
        }
        $products = $productsQuery->orderBy('name', 'asc')->paginate(15)->appends($request->query());
        return view('reports.stock', ['products' => $products]);
    }

    public function profitAndLossReport(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $data = $this->getProfitAndLossData($startDate, $endDate);
        $data['period'] = $request->input('period', $request->has('start_date') ? null : 'today');
        return view('reports.profit_and_loss', $data);
    }

    public function depositReport(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $totalDeposit = SaleDetail::whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->whereNull('deleted_at')->whereBetween('sale_date', [$startDate, $endDate]);
        })->sum(DB::raw('quantity * purchase_price'));
        
        $sales = Sale::whereNull('deleted_at')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->with('customer')
            ->withSum(['details as total_modal' => function ($query) {
                $query->select(DB::raw('SUM(quantity * purchase_price)'));
            }], 'total_modal')
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        return view('reports.deposits', [
            'sales' => $sales,
            'totalDeposit' => $totalDeposit,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'period' => $request->input('period', $request->has('start_date') ? null : 'today'),
        ]);
    }

    public function cashFlowReport(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $data = $this->getCashFlowData($startDate, $endDate);
        $data['period'] = $request->input('period', $request->has('start_date') ? null : 'today');
        return view('reports.cash_flow', $data);
    }

    //================================================================================
    // API & EXPORT METHODS
    //================================================================================

    public function getSaleDetails($id)
    {
        $sale = Sale::withTrashed()->with('details.product')->find($id);
        return response()->json($sale ? $sale->details : [], $sale ? 200 : 404);
    }

    public function getPurchaseDetails($id)
    {
        $purchase = Purchase::withTrashed()->with('details.product')->find($id);
        return response()->json($purchase ? $purchase->details : [], $purchase ? 200 : 404);
    }
    
    public function exportSalesCsv(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $fileName = 'laporan-penjualan-' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.csv';
        return Excel::download(new SalesExport($startDate, $endDate), $fileName);
    }

    public function exportSalesPdf(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $salesData = $this->getSalesData($startDate, $endDate);

        $sales = Sale::withTrashed()->with(['customer', 'details.product'])
            ->withSum(['details as total_modal' => fn($q) => $q->select(DB::raw('SUM(quantity * purchase_price)'))], 'total_modal')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->latest()->get();

        $pdf = Pdf::loadView('reports.pdf.sales', [
            'sales' => $sales,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ] + $salesData);

        return $pdf->stream('laporan-penjualan-' . $startDate->format('Y-m-d') . '.pdf');
    }

    public function exportPurchasesCsv(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $fileName = 'laporan-pembelian-' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.csv';
        return Excel::download(new PurchasesExport($startDate, $endDate), $fileName);
    }

    public function exportPurchasesPdf(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $purchasesData = $this->getPurchasesData($startDate, $endDate);

        $purchases = Purchase::withTrashed()->with(['supplier', 'details.product'])
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->latest()->get();

        $pdf = Pdf::loadView('reports.pdf.purchases', [
            'purchases' => $purchases,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ] + $purchasesData);
        
        return $pdf->stream('laporan-pembelian-' . $startDate->format('Y-m-d') . '.pdf');
    }

    public function exportStock(Request $request)
    {
        $search = $request->input('search');
        $fileName = 'laporan-stok-' . Carbon::now()->format('Y-m-d') . '.csv';
        return Excel::download(new StockExport($search), $fileName);
    }

    public function exportStockPdf(Request $request)
    {
        $search = $request->input('search');
        $productsQuery = Product::with(['category', 'type']);
        if ($search) {
            $productsQuery->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
        }
        $products = $productsQuery->orderBy('name', 'asc')->get();

        $pdf = Pdf::loadView('reports.pdf.stock', [
            'products' => $products,
            'search' => $search,
        ]);
        
        return $pdf->stream('laporan-stok-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function exportDepositsCsv(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $fileName = 'laporan-setoran-' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.csv';
        return Excel::download(new DepositsExport($startDate, $endDate), $fileName);
    }

    public function exportDepositsPdf(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        $totalDeposit = SaleDetail::whereHas('sale', fn($q) => $q->whereNull('deleted_at')->whereBetween('sale_date', [$startDate, $endDate]))
            ->sum(DB::raw('quantity * purchase_price'));
        
        $sales = Sale::whereNull('deleted_at')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->with(['customer', 'details.product'])
            ->withSum(['details as total_modal' => fn($q) => $q->select(DB::raw('SUM(quantity * purchase_price)'))], 'total_modal')
            ->latest()->get();

        $pdf = Pdf::loadView('reports.pdf.deposits', [
            'sales' => $sales,
            'totalDeposit' => $totalDeposit,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);

        return $pdf->stream('laporan-setoran-' . $startDate->format('Y-m-d') . '.pdf');
    }

    public function exportProfitAndLossCsv(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $data = $this->getProfitAndLossData($startDate, $endDate);
        $fileName = 'laporan-laba-rugi-' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.csv';
        return Excel::download(new ProfitAndLossExport($data), $fileName);
    }

    public function exportProfitAndLossPdf(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $data = $this->getProfitAndLossData($startDate, $endDate);
        $pdf = Pdf::loadView('reports.pdf.profit_and_loss', $data);
        return $pdf->stream('laporan-laba-rugi-' . $startDate->format('Y-m-d') . '.pdf');
    }
    
    private function exportCashFlowCsv(Carbon $startDate, Carbon $endDate)
    {
        $data = $this->getCashFlowData($startDate, $endDate);
        $fileName = 'laporan-arus-kas-' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.csv';
        return Excel::download(new CashFlowExport($data), $fileName);
    }

    private function exportCashFlowPdf(Carbon $startDate, Carbon $endDate)
    {
        $data = $this->getCashFlowData($startDate, $endDate);
        $pdf = Pdf::loadView('reports.pdf.cash_flow', $data);
        return $pdf->stream('laporan-arus-kas-' . $startDate->format('Y-m-d') . '.pdf');
    }

    private function getProfitAndLossData(Carbon $startDate, Carbon $endDate): array
    {
        // Query dasar untuk penjualan dan biaya (tidak berubah)
        $salesDetailsQuery = SaleDetail::whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->whereNull('deleted_at')->whereBetween('sale_date', [$startDate, $endDate]);
        });
        $expensesQuery = Expense::query()->whereBetween('expense_date', [$startDate, $endDate]);

        // Kalkulasi ringkasan (tidak berubah)
        $reportData = (clone $salesDetailsQuery)
            ->selectRaw('SUM(quantity * sale_price) as total_revenue, SUM(quantity * purchase_price) as total_cogs')
            ->first();

        $totalRevenue = $reportData->total_revenue ?? 0;
        $totalCogs = $reportData->total_cogs ?? 0;
        $totalExpenses = (clone $expensesQuery)->sum('amount');
        
        $expensesByCategory = (clone $expensesQuery)->with('category')
            ->select('expense_category_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('expense_category_id')
            ->get();

        // [BARU] Ambil data rincian untuk diekspor
        $sales = Sale::whereNull('deleted_at')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->with('customer')
            ->withSum(['details as total_modal' => fn($q) => $q->select(DB::raw('SUM(quantity * purchase_price)'))], 'total_modal')
            ->get();
            
        $expenses = (clone $expensesQuery)->with('category')->get();

        return [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'totalRevenue' => $totalRevenue,
            'totalCostOfGoods' => $totalCogs,
            'grossProfit' => $totalRevenue - $totalCogs,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $totalRevenue - $totalCogs - $totalExpenses,
            'expensesByCategory' => $expensesByCategory,
            'sales' => $sales, // <-- [BARU] Kirim rincian penjualan
            'expenses' => $expenses, // <-- [BARU] Kirim rincian biaya
        ];
    }

    private function getCashFlowData(Carbon $startDate, Carbon $endDate): array
    {
        $inflowsQuery = Payment::where('payable_type', Sale::class)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->whereHas('payable', fn($q) => $q->whereNull('deleted_at'));

        $purchaseOutflowsQuery = Payment::where('payable_type', Purchase::class)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->whereHas('payable', fn($q) => $q->whereNull('deleted_at'));

        $expenseOutflowsQuery = Expense::query()->whereBetween('expense_date', [$startDate, $endDate]);

        $receivablesQuery = Sale::whereNull('deleted_at')->where('payment_status', 'Belum Lunas')->whereBetween('sale_date', [$startDate, $endDate]);
        $payablesQuery = Purchase::whereNull('deleted_at')->where('payment_status', 'Belum Lunas')->whereBetween('purchase_date', [$startDate, $endDate]);

        $totalInflow = (clone $inflowsQuery)->sum('amount');
        $totalPurchaseOutflow = (clone $purchaseOutflowsQuery)->sum('amount');
        $totalExpenseOutflow = (clone $expenseOutflowsQuery)->sum('amount');
        $totalReceivables = (clone $receivablesQuery)->sum(DB::raw('total_amount - total_paid'));
        $totalPayables = (clone $payablesQuery)->sum(DB::raw('total_amount - total_paid'));

        return [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'totalInflow' => $totalInflow,
            'totalOutflow' => $totalPurchaseOutflow + $totalExpenseOutflow,
            'netCashFlow' => $totalInflow - ($totalPurchaseOutflow + $totalExpenseOutflow),
            'inflows' => (clone $inflowsQuery)->with('payable.customer')->latest('payment_date')->get(),
            'purchaseOutflows' => (clone $purchaseOutflowsQuery)->with('payable.supplier')->latest('payment_date')->get(),
            'expenseOutflows' => (clone $expenseOutflowsQuery)->with('category')->latest('expense_date')->get(),
            'totalReceivables' => $totalReceivables,
            'totalPayables' => $totalPayables,
            'receivables' => (clone $receivablesQuery)->with('customer')->latest('sale_date')->get(),
            'payables' => (clone $payablesQuery)->with('supplier')->latest('purchase_date')->get(),
        ];
    }
}