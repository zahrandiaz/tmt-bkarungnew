<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class ReceivableController extends Controller
{
    /**
     * Menampilkan daftar semua piutang (penjualan yang belum lunas).
     */
    public function index()
    {
        // Ambil semua penjualan dengan status 'belum lunas'
        // Muat juga relasi ke customer untuk ditampilkan di view
        $receivables = Sale::where('payment_status', 'belum lunas')
                            ->with('customer')
                            ->latest()
                            ->paginate(10);

        // Kirim data ke view
        return view('receivables.index', compact('receivables'));
    }
}