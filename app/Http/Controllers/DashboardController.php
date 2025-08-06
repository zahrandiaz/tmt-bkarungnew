<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product; // <-- PERBAIKAN: Menggunakan model Product
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
        // 1. Data Penjualan Hari Ini
        $todaySales = Sale::whereDate('created_at', Carbon::today())->get();
        $totalTodaySales = $todaySales->sum('total_amount');
        $countTodaySales = $todaySales->count();

        // 2. Data Pembelian Hari Ini
        $countTodayPurchases = Purchase::whereDate('created_at', Carbon::today())->count();

        // 3. Data Pelanggan
        $totalCustomers = Customer::count();

        // 4. Data Produk
        $totalProducts = Product::count(); // <-- PERBAIKAN: Menggunakan model Product

        return view('dashboard', [
            'totalTodaySales' => $totalTodaySales,
            'countTodaySales' => $countTodaySales,
            'countTodayPurchases' => $countTodayPurchases,
            'totalCustomers' => $totalCustomers,
            'totalProducts' => $totalProducts,
        ]);
    }
}