<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Http\Requests\StoreUserRequest; // <-- [BARU] Tambahkan FormRequest untuk create
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    public function index()
    {
        // [PERBAIKAN] Ambil input search ke dalam variabel
        $search = request('search');

        // Menambahkan pencarian
        $users = User::with('roles')
            ->when($search, function ($query, $search) { // [PERBAIKAN] Gunakan variabel $search
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->paginate(10)
            ->withQueryString(); // [PERBAIKAN] Tambahkan withQueryString()
            
        // [PERBAIKAN] Kirim variabel $search ke view
        return view('users.index', compact('users', 'search'));
    }

    // [BARU] Method untuk menampilkan form tambah pengguna
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    // [BARU] Method untuk menyimpan pengguna baru
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole(Role::findById($validated['role']));

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Menggunakan syncRoles untuk memperbarui role
        $user->syncRoles(Role::findById($validated['role']));

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}