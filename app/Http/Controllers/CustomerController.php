<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest; // <-- [BARU] Tambahkan ini
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::latest()->paginate(10);
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $validatedData = $request->validated();
        Customer::create($validatedData);
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    // [MODIFIKASI] Ganti parameter dengan request dan model yang sesuai
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        // [BARU] Ambil data yang sudah tervalidasi
        $validatedData = $request->validated();
        
        // [BARU] Update data pelanggan
        $customer->update($validatedData);

        // [BARU] Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('customers.index')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    // [MODIFIKASI] Ganti parameter dengan model yang sesuai
    public function destroy(Customer $customer)
    {
        // [BARU] Hapus data dari database
        $customer->delete();

        // [BARU] Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus.');
    }
}