<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReceivableController extends Controller
{
    /**
     * Menampilkan daftar semua piutang berdasarkan status.
     */
    public function index(Request $request)
    {
        // [UBAH] Tambahkan logika untuk filter status
        $status = $request->query('status', 'belum lunas'); // Default ke 'belum lunas'
        $query = Sale::query();

        if ($status == 'lunas') {
            $query->where('payment_status', 'lunas');
        } else {
            $query->where('payment_status', 'belum lunas');
        }
        
        $receivables = $query->with('customer')->latest()->paginate(10)->withQueryString();

        return view('receivables.index', compact('receivables'));
    }

    /**
     * Menampilkan halaman untuk mengelola pembayaran piutang.
     */
    public function manage(Sale $sale)
    {
        $sale->load('customer', 'payments.user');
        return view('receivables.manage', compact('sale'));
    }

    /**
     * Menyimpan catatan pembayaran baru untuk piutang.
     */
    public function storePayment(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $remainingAmount = $sale->total_amount - $sale->total_paid;
        if ($validated['amount'] > $remainingAmount) {
            return back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan.');
        }

        try {
            DB::transaction(function () use ($validated, $sale, $request) {
                $attachmentPath = null;
                if ($request->hasFile('attachment')) {
                    $attachmentPath = $request->file('attachment')->store('payment_proofs', 'public');
                }

                $sale->payments()->create([
                    'amount' => $validated['amount'],
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'notes' => $validated['notes'],
                    'attachment_path' => $attachmentPath,
                    'user_id' => $request->user()->id,
                ]);

                $sale->total_paid += $validated['amount'];

                if ($sale->total_paid >= $sale->total_amount) {
                    $sale->payment_status = 'lunas';
                }

                $sale->save();
            });

            // Jika lunas, arahkan kembali ke daftar piutang lunas
            if($sale->payment_status == 'lunas') {
                return redirect()->route('receivables.index', ['status' => 'lunas'])->with('success', 'Pembayaran berhasil dicatat. Piutang telah lunas.');
            }

            return redirect()->route('receivables.manage', $sale)->with('success', 'Pembayaran berhasil dicatat.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}