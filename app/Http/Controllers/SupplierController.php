<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use Illuminate\Http\Request; // [BARU V1.14.0] Import Request

class SupplierController extends Controller
{
    /**
     * [MODIFIKASI V1.14.0] Tambahkan logika pencarian.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $suppliersQuery = Supplier::query();

        if ($search) {
            $suppliersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $suppliers = $suppliersQuery->latest()->paginate(10)->withQueryString();

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request)
    {
        Supplier::create($request->validated());
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        // Biasanya tidak digunakan dalam CRUD resource standar, bisa dibiarkan kosong
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());
        return redirect()->route('suppliers.index')->with('success', 'Data supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            return redirect()->route('suppliers.index')->with('error', 'Supplier tidak dapat dihapus karena sudah memiliki riwayat transaksi.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
    }
}