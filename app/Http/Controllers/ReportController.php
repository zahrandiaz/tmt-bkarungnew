<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\SaleDetail;
use Carbon\Carbon;
use App\Exports\SalesExport;
use App\Exports\PurchasesExport;
use App\Exports\StockExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Menampilkan halaman laporan penjualan dengan filter.
     */
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
     * [DIPERBAIKI] Menampilkan halaman laporan laba rugi sederhana dengan filter.
     */
    public function profitAndLossReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $totalRevenue = 0;
        $totalCostOfGoods = 0;

        $salesQuery = Sale::query()->with('details.product');

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $salesQuery->whereBetween('created_at', [$start, $end]);
        }

        $sales = $salesQuery->get();

        foreach ($sales as $sale) {
            foreach ($sale->details as $detail) {
                // Gunakan 'sale_price' sesuai model SaleDetail
                $totalRevenue += ($detail->quantity * $detail->sale_price);
                if ($detail->product) {
                    $totalCostOfGoods += ($detail->quantity * $detail->product->purchase_price);
                }
            }
        }

        $totalProfit = $totalRevenue - $totalCostOfGoods;

        return view('reports.profit_and_loss', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalRevenue' => $totalRevenue,
            'totalCostOfGoods' => $totalCostOfGoods,
            'totalProfit' => $totalProfit,
        ]);
    }

    /**
     * [BARU] Menangani permintaan ekspor untuk Laporan Penjualan.
     */
    public function exportSales(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $fileName = 'laporan-penjualan-' . Carbon::now()->format('Y-m-d') . '.csv';

        return Excel::download(new SalesExport($startDate, $endDate), $fileName);
    }

    /**
     * [BARU] Menangani permintaan ekspor untuk Laporan Pembelian.
     */
    public function exportPurchases(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $fileName = 'laporan-pembelian-' . Carbon::now()->format('Y-m-d') . '.csv';

        return Excel::download(new PurchasesExport($startDate, $endDate), $fileName);
    }

    /**
     * [BARU] Menangani permintaan ekspor untuk Laporan Stok.
     */
    public function exportStock()
    {
        $fileName = 'laporan-stok-' . Carbon::now()->format('Y-m-d') . '.csv';
        return Excel::download(new StockExport(), $fileName);
    }
}