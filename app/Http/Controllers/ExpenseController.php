<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = Expense::with('category')->latest();
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }
        $expenses = $query->paginate(10)->withQueryString();
        return view('expenses.index', compact('expenses', 'search'));
    }

    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.create', compact('categories'));
    }

    public function store(StoreExpenseRequest $request)
    {
        $validated = $request->validated();
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $image = $request->file('attachment');
            $fileName = time() . '_' . Str::random(10) . '.webp';
            $imageCompressed = Image::read($image->getRealPath())->toWebp(75);
            Storage::disk('public')->put('expense_proofs/' . $fileName, (string) $imageCompressed);
            $attachmentPath = 'expense_proofs/' . $fileName;
        }

        Expense::create([
            'name' => $validated['name'],
            'expense_date' => $validated['expense_date'],
            'expense_category_id' => $validated['expense_category_id'],
            'amount' => $validated['amount'],
            // FINAL FIX: Gunakan null coalescing operator untuk semua field opsional
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'attachment_path' => $attachmentPath,
            'user_id' => $request->user()->id,
        ]);
        return redirect()->route('expenses.index')->with('success', 'Biaya berhasil ditambahkan.');
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $validated = $request->validated();
        $attachmentPath = $expense->attachment_path;
        if ($request->hasFile('attachment')) {
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }
            $image = $request->file('attachment');
            $fileName = time() . '_' . Str::random(10) . '.webp';
            $imageCompressed = Image::read($image->getRealPath())->toWebp(75);
            Storage::disk('public')->put('expense_proofs/' . $fileName, (string) $imageCompressed);
            $attachmentPath = 'expense_proofs/' . $fileName;
        }

        $expense->update([
            'name' => $validated['name'],
            'expense_date' => $validated['expense_date'],
            'expense_category_id' => $validated['expense_category_id'],
            'amount' => $validated['amount'],
             // FINAL FIX: Gunakan null coalescing operator untuk semua field opsional
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'attachment_path' => $attachmentPath,
        ]);
        return redirect()->route('expenses.index')->with('success', 'Biaya berhasil diperbarui.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->attachment_path) {
            Storage::disk('public')->delete($expense->attachment_path);
        }
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Biaya berhasil dihapus.');
    }
}