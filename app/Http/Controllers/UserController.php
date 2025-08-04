<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role; // <-- 1. TAMBAHKAN INI

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // 2. Ambil semua peran yang ada
        $roles = Role::all();
        // 3. Tampilkan view dan kirim data user beserta roles
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // 4. Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|exists:roles,name' // Pastikan peran yang dipilih ada di tabel roles
        ]);

        // 5. Update nama dan email pengguna
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // 6. Sinkronkan peran pengguna. Ini akan menghapus peran lama dan menerapkan yang baru.
        $user->syncRoles($validated['role']);

        // 7. Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
