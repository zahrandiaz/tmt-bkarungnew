<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Expense;
use Carbon\Carbon;
use App\Exports\SalesExport;
use App\Exports\PurchasesExport;
use App\Exports\StockExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

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
        
        $salesQuery = Sale::query()->with('customer'); // [MODIFIKASI V1.13.0] Hapus withTrashed untuk kalkulasi statistik
        
        if ($startDate && $endDate) {
            $salesQuery->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        }

        // [BARU V1.13.0] Kalkulasi Statistik
        $statsQuery = (clone $salesQuery); // Clone query sebelum paginasi
        $totalTransactions = $statsQuery->count();
        $totalRevenue = $statsQuery->sum('total_amount');

        // Kalkulasi HPP dari sale_details yang terkait
        $totalCogs = SaleDetail::whereHas('sale', function ($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
            }
        })->sum(DB::raw('quantity * purchase_price'));
        
        $grossProfit = $totalRevenue - $totalCogs;

        // Ambil data untuk tabel dengan paginasi (termasuk yang dibatalkan)
        $tableQuery = Sale::withTrashed()->with('customer');
        if ($startDate && $endDate) {
            $tableQuery->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        }
        $sales = $tableQuery->latest()->paginate(10)->appends($request->query());

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
        
        $purchasesQuery = Purchase::query(); // [MODIFIKASI V1.13.0] Hapus withTrashed untuk kalkulasi statistik

        if ($startDate && $endDate) {
            $purchasesQuery->whereBetween('purchase_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        }

        // [BARU V1.13.0] Kalkulasi Statistik
        $statsQuery = (clone $purchasesQuery);
        $totalTransactions = $statsQuery->count();
        $totalExpenditure = $statsQuery->sum('total_amount');
        
        // Ambil data untuk tabel dengan paginasi (termasuk yang dibatalkan)
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