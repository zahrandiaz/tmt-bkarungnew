<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
// Hapus 'use Illuminate\Http\Request;' karena tidak digunakan

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->paginate(10);
        return view('suppliers.index', compact('suppliers'));
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

    // [MODIFIKASI] Tambahkan validasi sebelum hapus
    public function destroy(Supplier $supplier)
    {
        // Cek apakah supplier ini memiliki transaksi pembelian terkait
        if ($supplier->purchases()->exists()) {
            return redirect()->route('suppliers.index')->with('error', 'Supplier tidak dapat dihapus karena sudah memiliki riwayat transaksi.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
    }
}