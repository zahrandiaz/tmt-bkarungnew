<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
// [BARU V1.10.0] Import class yang diperlukan untuk kompresi gambar
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    /**
     * Menampilkan daftar semua biaya.
     */
    public function index()
    {
        $expenses = Expense::with('category')->latest()->paginate(10);
        return view('expenses.index', compact('expenses'));
    }

    /**
     * Menampilkan form untuk membuat biaya baru.
     */
    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.create', compact('categories'));
    }

    /**
     * Menyimpan biaya baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $attachmentPath = null;
        // [MODIFIKASI V1.10.0] Logika kompresi gambar saat membuat data baru
        if ($request->hasFile('attachment')) {
            $image = $request->file('attachment');
            $fileName = time() . '_' . Str::random(10) . '.webp';
            $imageCompressed = Image::read($image->getRealPath())->toWebp(75);
            Storage::disk('public')->put('expense_attachments/' . $fileName, (string) $imageCompressed);
            $attachmentPath = 'expense_attachments/' . $fileName;
        }

        Expense::create([
            'expense_category_id' => $validated['expense_category_id'],
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'notes' => $validated['notes'],
            'attachment_path' => $attachmentPath,
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Catatan biaya berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit biaya.
     */
    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    /**
     * Memperbarui biaya di database.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $attachmentPath = $expense->attachment_path;
        // [MODIFIKASI V1.10.0] Logika kompresi gambar saat memperbarui data
        if ($request->hasFile('attachment')) {
            // Hapus file lama jika ada
            if ($expense->attachment_path) {
                Storage::disk('public')->delete($expense->attachment_path);
            }
            // Proses dan simpan file baru yang sudah dikompresi
            $image = $request->file('attachment');
            $fileName = time() . '_' . Str::random(10) . '.webp';
            $imageCompressed = Image::read($image->getRealPath())->toWebp(75);
            Storage::disk('public')->put('expense_attachments/' . $fileName, (string) $imageCompressed);
            $attachmentPath = 'expense_attachments/' . $fileName;
        }

        $expense->update([
            'expense_category_id' => $validated['expense_category_id'],
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'notes' => $validated['notes'],
            'attachment_path' => $attachmentPath,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Catatan biaya berhasil diperbarui.');
    }

    /**
     * Menghapus biaya dari database.
     */
    public function destroy(Expense $expense)
    {
        if ($expense->attachment_path) {
            Storage::disk('public')->delete($expense->attachment_path);
        }
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Catatan biaya berhasil dihapus.');
    }
}