<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // [OPTIMALISASI & PERBAIKAN] Gunakan query agregat dan kolom tanggal yang benar
        $salesData = Sale::whereDate('sale_date', Carbon::today())
            ->selectRaw('SUM(total_amount) as total, COUNT(*) as count')
            ->first();

        // [PERBAIKAN] Gunakan kolom tanggal yang benar
        $countTodayPurchases = Purchase::whereDate('purchase_date', Carbon::today())->count();

        // Data Pelanggan
        $totalCustomers = Customer::count();

        // Data Produk
        $totalProducts = Product::count();

        // Data Produk Stok Kritis (Logika ini sudah benar dan efisien)
        $criticalStockProducts = Product::where('stock', '<=', DB::raw('min_stock_level'))
                                        ->where('min_stock_level', '>', 0)
                                        ->orderBy('stock', 'asc')
                                        ->get();

        return view('dashboard', [
            'totalTodaySales' => $salesData->total ?? 0,
            'countTodaySales' => $salesData->count ?? 0,
            'countTodayPurchases' => $countTodayPurchases,
            'totalCustomers' => $totalCustomers,
            'totalProducts' => $totalProducts,
            'criticalStockProducts' => $criticalStockProducts,
        ]);
    }
}