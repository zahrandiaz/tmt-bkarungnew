<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    /**
     * Menampilkan daftar semua utang (pembelian yang belum lunas).
     */
    public function index()
    {
        // Ambil semua pembelian dengan status 'belum lunas'
        // Muat juga relasi ke supplier untuk ditampilkan di view
        $debts = Purchase::where('payment_status', 'belum lunas')
                         ->with('supplier')
                         ->latest()
                         ->paginate(10);

        // Kirim data ke view
        return view('debts.index', compact('debts'));
    }
}