<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase; // <-- [BARU] Tambahkan ini
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Menampilkan halaman laporan penjualan dengan filter.
     */
    public function salesReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Query dasar untuk mengambil data penjualan, termasuk yang di-soft-delete (dibatalkan)
        // dan relasi dengan customer.
        $salesQuery = Sale::withTrashed()->with('customer');

        // Terapkan filter tanggal jika ada input
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

    /**
     * [BARU] Menampilkan halaman laporan pembelian dengan filter.
     */
    public function purchasesReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Query dasar untuk mengambil data pembelian, termasuk yang di-soft-delete (dibatalkan)
        // dan relasi dengan supplier.
        $purchasesQuery = Purchase::withTrashed()->with('supplier');

        // Terapkan filter tanggal jika ada input
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
}