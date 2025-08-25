<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Http\Requests\StoreDebtPaymentRequest; // [UBAH]
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'belum lunas');
        $search = $request->query('search');
        $query = Purchase::query();
        if ($status == 'lunas') {
            $query->where('payment_status', 'Lunas');
        } else {
            $query->where('payment_status', 'Belum Lunas');
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('purchase_code', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }
        $debts = $query->with('supplier')->latest()->paginate(10)->withQueryString();
        return view('debts.index', compact('debts', 'search'));
    }

    public function manage(Purchase $purchase)
    {
        $purchase->load('supplier', 'payments.user');
        return view('debts.manage', compact('purchase'));
    }

    // [UBAH] Gunakan Form Request baru
    public function storePayment(StoreDebtPaymentRequest $request, Purchase $purchase)
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated, $purchase, $request) {
                $attachmentPath = null;
                if ($request->hasFile('attachment')) {
                    $image = $request->file('attachment');
                    $fileName = time() . '_' . Str::random(10) . '.webp';
                    $imageCompressed = Image::read($image->getRealPath())->toWebp(75);
                    Storage::disk('public')->put('payment_proofs/' . $fileName, (string) $imageCompressed);
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

                // Gunakan DB::raw untuk mencegah race condition
                $purchase->increment('total_paid', $validated['amount']);

                // Refresh model untuk mendapatkan nilai total_paid yang terbaru
                $purchase->refresh();

                if ($purchase->total_paid >= $purchase->total_amount - 0.001) {
                    $purchase->payment_status = 'Lunas';
                }

                $purchase->save();
            });

            if($purchase->payment_status == 'Lunas') {
                return redirect()->route('debts.index', ['status' => 'lunas'])->with('success', 'Pembayaran berhasil dicatat. Utang telah lunas.');
            }
            return redirect()->route('debts.manage', $purchase)->with('success', 'Pembayaran berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}