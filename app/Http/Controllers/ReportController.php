<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Expense;
use Carbon\Carbon;
use App\Exports\SalesExport;
use App\Exports\PurchasesExport;
use App\Exports\StockExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * [BARU V1.10.0] Method privat untuk menentukan rentang tanggal dari parameter.
     */
    private function getDateRange(Request $request)
    {
        // Prioritaskan filter cepat
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

        // Jika tidak ada filter cepat, gunakan filter manual
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
            return [$startDate, $endDate];
        }
        
        // Default jika tidak ada filter sama sekali: hari ini
        return [Carbon::today(), Carbon::today()];
    }

    public function salesReport(Request $request)
    {
        // [MODIFIKASI V1.10.0] Gunakan method baru untuk mendapatkan rentang tanggal
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
            'period' => $request->input('period', $request->has('start_date') ? null : 'today'), // Untuk menandai tombol aktif
        ]);
    }

    public function purchasesReport(Request $request)
    {
        // [MODIFIKASI V1.10.0] Gunakan method baru untuk mendapatkan rentang tanggal
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
            'period' => $request->input('period', $request->has('start_date') ? null : 'today'), // Untuk menandai tombol aktif
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

    public function profitAndLossReport(Request $request)
    {
        // [MODIFIKASI V1.10.0] Gunakan method baru untuk mendapatkan rentang tanggal
        [$startDate, $endDate] = $this->getDateRange($request);
        
        $salesQuery = Sale::query()->where('payment_status', 'Lunas');
        $expensesQuery = Expense::query();

        if ($startDate && $endDate) {
            $startOfDay = $startDate->copy()->startOfDay();
            $endOfDay = $endDate->copy()->endOfDay();
            $salesQuery->whereBetween('sale_date', [$startOfDay, $endOfDay]);
            $expensesQuery->whereBetween('expense_date', [$startOfDay, $endOfDay]);
        }

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

        $totalExpenses = $expensesQuery->sum('amount');
        
        $grossProfit = $totalRevenue - $totalCostOfGoods;
        $netProfit = $grossProfit - $totalExpenses;

        return view('reports.profit_and_loss', [
            'startDate' => $startDate ? $startDate->format('Y-m-d') : null,
            'endDate' => $endDate ? $endDate->format('Y-m-d') : null,
            'period' => $request->input('period', $request->has('start_date') ? null : 'today'), // Untuk menandai tombol aktif
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