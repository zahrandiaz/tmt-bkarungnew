<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
// [BARU V1.10.0] Import class yang diperlukan untuk kompresi gambar
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

class ReceivableController extends Controller
{
    /**
     * Menampilkan daftar semua piutang berdasarkan status.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'belum lunas'); 
        $query = Sale::query();

        if ($status == 'lunas') {
            $query->where('payment_status', 'Lunas');
        } else {
            $query->where('payment_status', 'Belum Lunas');
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
        // Tambahkan toleransi kecil untuk masalah floating point
        if ($validated['amount'] > $remainingAmount + 0.001) {
            return back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan.');
        }

        try {
            DB::transaction(function () use ($validated, $sale, $request) {
                $attachmentPath = null;

                // [MODIFIKASI V1.10.0] Logika kompresi gambar
                if ($request->hasFile('attachment')) {
                    $image = $request->file('attachment');
                    // Buat nama file unik dengan format webp
                    $fileName = time() . '_' . Str::random(10) . '.webp';
                    // Kompresi dan simpan gambar
                    $imageCompressed = Image::read($image->getRealPath())->toWebp(75);
                    Storage::disk('public')->put('payment_proofs/' . $fileName, (string) $imageCompressed);
                    // Simpan path yang benar
                    $attachmentPath = 'payment_proofs/' . $fileName;
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

                // Gunakan perbandingan dengan toleransi untuk menghindari masalah floating point
                if ($sale->total_paid >= $sale->total_amount - 0.001) {
                    $sale->payment_status = 'Lunas';
                }

                $sale->save();
            });

            // Sesuaikan pesan dan pengalihan berdasarkan status pembayaran
            if($sale->payment_status == 'Lunas') {
                return redirect()->route('receivables.index', ['status' => 'lunas'])->with('success', 'Pembayaran berhasil dicatat. Piutang telah lunas.');
            }

            return redirect()->route('receivables.manage', $sale)->with('success', 'Pembayaran berhasil dicatat.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}