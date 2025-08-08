<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
// Hapus 'use Illuminate\Http\Request;' karena tidak digunakan

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->paginate(10);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        // Bisa dibiarkan kosong
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        return redirect()->route('customers.index')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    // [MODIFIKASI] Tambahkan validasi sebelum hapus
    public function destroy(Customer $customer)
    {
        // Cek apakah pelanggan ini memiliki transaksi penjualan terkait
        if ($customer->sales()->exists()) {
            return redirect()->route('customers.index')->with('error', 'Pelanggan tidak dapat dihapus karena sudah memiliki riwayat transaksi.');
        }

        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus.');
    }
}