<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
// [BARU V1.10.0] Import class yang diperlukan untuk kompresi gambar
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

class DebtController extends Controller
{
    /**
     * Menampilkan daftar semua utang (pembelian yang belum lunas).
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'belum lunas'); // Default ke 'belum lunas'
        $query = Purchase::query();

        if ($status == 'lunas') {
            $query->where('payment_status', 'Lunas');
        } else {
            $query->where('payment_status', 'Belum Lunas');
        }

        $debts = $query->with('supplier')->latest()->paginate(10)->withQueryString();

        return view('debts.index', compact('debts'));
    }

    /**
     * Menampilkan halaman untuk mengelola pembayaran utang.
     */
    public function manage(Purchase $purchase)
    {
        $purchase->load('supplier', 'payments.user');
        return view('debts.manage', compact('purchase'));
    }

    /**
     * Menyimpan catatan pembayaran baru untuk utang.
     */
    public function storePayment(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $remainingAmount = $purchase->total_amount - $purchase->total_paid;
        // Tambahkan toleransi kecil untuk masalah floating point
        if ($validated['amount'] > $remainingAmount + 0.001) {
            return back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan.');
        }

        try {
            DB::transaction(function () use ($validated, $purchase, $request) {
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

                $purchase->payments()->create([
                    'amount' => $validated['amount'],
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'notes' => $validated['notes'],
                    'attachment_path' => $attachmentPath,
                    'user_id' => $request->user()->id,
                ]);

                $purchase->total_paid += $validated['amount'];

                // Gunakan perbandingan dengan toleransi untuk menghindari masalah floating point
                if ($purchase->total_paid >= $purchase->total_amount - 0.001) {
                    $purchase->payment_status = 'Lunas';
                }

                $purchase->save();
            });

            // Jika lunas, arahkan kembali ke daftar utang lunas
            if($purchase->payment_status == 'Lunas') {
                return redirect()->route('debts.index', ['status' => 'lunas'])->with('success', 'Pembayaran berhasil dicatat. Utang telah lunas.');
            }

            return redirect()->route('debts.manage', $purchase)->with('success', 'Pembayaran berhasil dicatat.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}