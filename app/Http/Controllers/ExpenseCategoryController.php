<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    /**
     * Menampilkan daftar semua kategori biaya.
     */
    public function index()
    {
        $categories = ExpenseCategory::latest()->paginate(10);
        return view('expense_categories.index', compact('categories'));
    }

    /**
     * Menampilkan form untuk membuat kategori biaya baru.
     */
    public function create()
    {
        return view('expense_categories.create');
    }

    /**
     * Menyimpan kategori biaya baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:expense_categories|max:255',
            'description' => 'nullable|string',
        ]);

        ExpenseCategory::create($validated);

        return redirect()->route('expense-categories.index')->with('success', 'Kategori biaya berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit kategori biaya.
     */
    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expense_categories.edit', compact('expenseCategory'));
    }

    /**
     * Memperbarui kategori biaya di database.
     */
    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id,
            'description' => 'nullable|string',
        ]);

        $expenseCategory->update($validated);

        return redirect()->route('expense-categories.index')->with('success', 'Kategori biaya berhasil diperbarui.');
    }

    /**
     * Menghapus kategori biaya dari database.
     */
    public function destroy(ExpenseCategory $expenseCategory)
    {
        // Tambahkan validasi di masa depan untuk mencegah penghapusan jika sudah terpakai
        $expenseCategory->delete();
        return redirect()->route('expense-categories.index')->with('success', 'Kategori biaya berhasil dihapus.');
    }
}