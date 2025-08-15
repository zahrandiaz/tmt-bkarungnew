<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Menampilkan halaman matriks hak akses.
     */
    public function index()
    {
        // Ambil semua peran kecuali 'Admin', karena Admin harus selalu memiliki semua akses
        $roles = Role::where('name', '!=', 'Admin')->with('permissions')->get();
        
        // Ambil semua permission dan kelompokkan berdasarkan prefix (misal: 'user-', 'product-')
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('-', $permission->name)[0]; // Mengelompokkan berdasarkan kata pertama
        });

        return view('permissions.index', compact('roles', 'permissions'));
    }

    /**
     * Memperbarui hak akses untuk semua peran.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'nullable|array', // Memastikan setiap role memiliki array permission
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Ambil semua peran yang bisa di-update
                $roles = Role::where('name', '!=', 'Admin')->get();

                foreach ($roles as $role) {
                    // Ambil permissions yang dicentang untuk peran ini dari request,
                    // atau array kosong jika tidak ada yang dicentang.
                    $permissionsForRole = $validated['permissions'][$role->id] ?? [];
                    
                    // Gunakan syncPermissions untuk memperbarui. 
                    // Ini akan menghapus permission lama dan menambahkan yang baru.
                    $role->syncPermissions($permissionsForRole);
                }
            });

            return redirect()->route('permissions.index')->with('success', 'Hak akses berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat memperbarui hak akses: ' . $e->getMessage());
        }
    }
}