<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role; // <-- 1. TAMBAHKAN INI

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
        ]);

        Role::create($validated);

        return redirect()->route('roles.index')->with('success', 'Peran baru berhasil ditambahkan.');
    }

    public function edit(Role $role)
    {
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id . '|max:255',
        ]);

        $role->update($validated);

        return redirect()->route('roles.index')->with('success', 'Peran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Prevent deleting core roles
        if (in_array($role->name, ['Admin', 'Manager', 'Staf'])) {
            return redirect()->route('roles.index')->with('error', 'Peran inti tidak dapat dihapus.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Peran berhasil dihapus.');
    }
}