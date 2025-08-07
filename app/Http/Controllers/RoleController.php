<?php

namespace App\Http\Controllers;

// Hapus 'use Illuminate\Http\Request;' karena sudah tidak dipakai di store/update
use Spatie\Permission\Models\Role;
// [BARU] Tambahkan FormRequest yang kita buat
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    // [MODIFIKASI] Ganti Request dengan StoreRoleRequest
    public function store(StoreRoleRequest $request)
    {
        // Validasi sudah terjadi secara otomatis sebelum masuk ke sini
        Role::create($request->validated());

        return redirect()->route('roles.index')->with('success', 'Peran baru berhasil ditambahkan.');
    }

    public function edit(Role $role)
    {
        return view('roles.edit', compact('role'));
    }

    // [MODIFIKASI] Ganti Request dengan UpdateRoleRequest
    public function update(UpdateRoleRequest $request, Role $role)
    {
        // Validasi juga sudah terjadi secara otomatis
        $role->update($request->validated());

        return redirect()->route('roles.index')->with('success', 'Peran berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['Admin', 'Manager', 'Staf'])) {
            return redirect()->route('roles.index')->with('error', 'Peran inti tidak dapat dihapus.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Peran berhasil dihapus.');
    }
}