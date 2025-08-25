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
        $search = $request->input('search');
        $expensesQuery = Expense::with('category');
        if ($search) {
            $expensesQuery->where('name', 'like', "%{$search}%");
        }
        $expenses = $expensesQuery->latest()->paginate(10)->appends(['search' => $search]);
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
            if ($expense->attachment_path) {
                Storage::disk('public')->delete($expense->attachment_path);
            }
            $image = $request->file('attachment');
            $fileName = time() . '_' . Str::random(10) . '.webp';
            $imageCompressed = Image::read($image->getRealPath())->toWebp(75);
            
            // [PERBAIKAN] Menghapus satu tanda kutip ' yang berlebih
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

    public function destroy(Expense $expense)
    {
        if ($expense->attachment_path) {
            Storage::disk('public')->delete($expense->attachment_path);
        }
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Catatan biaya berhasil dihapus.');
    }
}