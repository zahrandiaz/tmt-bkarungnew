<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Http\Request; // [BARU V1.14.0] Import Request

class CustomerController extends Controller
{
    /**
     * [MODIFIKASI V1.14.0] Tambahkan logika pencarian.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customersQuery = Customer::query();

        if ($search) {
            $customersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $customersQuery->latest()->paginate(10)->withQueryString();

        return view('customers.index', compact('customers', 'search'));
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

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            return redirect()->route('customers.index')->with('error', 'Pelanggan tidak dapat dihapus karena sudah memiliki riwayat transaksi.');
        }

        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus.');
    }
}