<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleDetail; // [BARU V1.12.0] Import SaleDetail
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
        $salesQuery = Sale::withTrashed()->with('customer');
        if ($startDate && $endDate) {
            $salesQuery->whereBetween('sale_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        }
        $sales = $salesQuery->latest()->paginate(10)->appends($request->query());
        return view('reports.sales', [
            'sales' => $sales,
            'startDate' => $startDate ? $startDate->format('Y-m-d') : null,
            'endDate' => $endDate ? $endDate->format('Y-m-d') : null,
            'period' => $request->input('period', $request->has('start_date') ? null : 'today'),
        ]);
    }

    public function purchasesReport(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $purchasesQuery = Purchase::withTrashed()->with('supplier');
        if ($startDate && $endDate) {
            $purchasesQuery->whereBetween('purchase_date', [$startDate->startOfDay(), $endDate->endOfDay()]);
        }
        $purchases = $purchasesQuery->latest()->paginate(10)->appends($request->query());
        return view('reports.purchases', [
            'purchases' => $purchases,
            'startDate' => $startDate ? $startDate->format('Y-m-d') : null,
            'endDate' => $endDate ? $endDate->format('Y-m-d') : null,
            'period' => $request->input('period', $request->has('start_date') ? null : 'today'),
        ]);
    }

    public function stockReport()
    {
        $products = Product::with(['category', 'type'])->orderBy('name', 'asc')->paginate(15);
        return view('reports.stock', ['products' => $products]);
    }

    // [MODIFIKASI V1.12.0] Rombak total logika perhitungan laba rugi
    public function profitAndLossReport(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        // Query builder dasar untuk penjualan yang sudah lunas dan memiliki detail
        $salesDetailsQuery = SaleDetail::query()
            ->whereHas('sale', function ($query) {
                $query->where('payment_status', 'Lunas');
            });

        $expensesQuery = Expense::query(); 

        if ($startDate && $endDate) {
            $startOfDay = $startDate->copy()->startOfDay();
            $endOfDay = $endDate->copy()->endOfDay();
            
            // Filter sale_details berdasarkan tanggal di relasi sale
            $salesDetailsQuery->whereHas('sale', function ($query) use ($startOfDay, $endOfDay) {
                $query->whereBetween('sale_date', [$startOfDay, $endOfDay]);
            });

            $expensesQuery->whereBetween('expense_date', [$startOfDay, $endOfDay]);
        }

        // [LOGIKA BARU] Hitung HPP dan Pendapatan dengan satu query efisien
        $reportData = $salesDetailsQuery
            ->select(
                DB::raw('SUM(quantity * sale_price) as total_revenue'),
                DB::raw('SUM(quantity * purchase_price) as total_cogs') // HPP Akurat!
            )
            ->first();

        $totalRevenue = $reportData->total_revenue ?? 0;
        $totalCostOfGoods = $reportData->total_cogs ?? 0;

        $totalExpenses = $expensesQuery->sum('amount');
        
        $expensesByCategory = (clone $expensesQuery)->with('category') // Clone untuk query terpisah
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

    // ... method export tetap sama ...
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