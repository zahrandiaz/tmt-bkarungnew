<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
// Hapus 'use Illuminate\Http\Request;'
use App\Http\Requests\UpdateUserRequest; // <-- [BARU] Tambahkan FormRequest

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(10); // [MODIFIKASI] Tambahkan paginasi
        return view('users.index', compact('users'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    // [MODIFIKASI] Ganti Request dengan UpdateUserRequest
    public function update(UpdateUserRequest $request, User $user)
    {
        // Validasi sudah terjadi secara otomatis
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->syncRoles($validated['role']);

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    // [MODIFIKASI] Tambahkan logika hapus dengan pengamanan
    public function destroy(User $user)
    {
        // Jangan biarkan pengguna menghapus diri sendiri
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}