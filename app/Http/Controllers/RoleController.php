<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission; // <-- [BARU] Tambahkan ini
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Support\Facades\DB; // <-- [BARU] Tambahkan ini

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::paginate(10);
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        // [MODIFIKASI] Kirim data permissions ke view
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('-', $permission->name)[0]; // Mengelompokkan berdasarkan modul
        });
        return view('roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        // [MODIFIKASI] Tambahkan DB Transaction dan sync permissions
        DB::transaction(function () use ($request) {
            $role = Role::create(['name' => $request->name]);
            $role->syncPermissions($request->input('permissions', []));
        });

        return redirect()->route('roles.index')->with('success', 'Peran baru berhasil ditambahkan.');
    }

    public function edit(Role $role)
    {
        // [MODIFIKASI] Kirim data permissions ke view
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('-', $permission->name)[0];
        });
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        // [MODIFIKASI] Tambahkan DB Transaction dan sync permissions
        DB::transaction(function () use ($request, $role) {
            $role->update(['name' => $request->name]);
            $role->syncPermissions($request->input('permissions', []));
        });

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