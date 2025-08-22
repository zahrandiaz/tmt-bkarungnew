<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar semua permissions
        $permissions = [
            // Manajemen Pengguna & Peran
            'user-view', 'user-create', 'user-edit', 'user-delete',
            'role-view', 'role-create', 'role-edit', 'role-delete',

            // Manajemen Produk & Master Data
            'product-view', 'product-create', 'product-edit', 'product-delete',

            // Transaksi
            'transaction-view', 'transaction-create', 'transaction-cancel', 
            'transaction-delete-permanent', 'transaction-restore',

            // Keuangan
            'finance-view', 'finance-manage-payment', 'finance-crud-expense',

            // Laporan
            'report-view-all',

            // Fitur Lanjutan
            'adjustment-price', 'adjustment-stock',

            // [BARU] Log Aktivitas
            'log-view', 'log-delete',
        ];

        // [MODIFIKASI V2.0.0] Gunakan firstOrCreate untuk mencegah eror duplikat
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Ambil Roles
        $adminRole = Role::findByName('Admin');
        $managerRole = Role::findByName('Manager');
        $stafRole = Role::findByName('Staf');

        // Berikan semua permission ke Admin
        $adminRole->givePermissionTo(Permission::all());

        // Berikan permission spesifik ke Manager
        $managerRole->givePermissionTo([
            'product-view', 'product-create', 'product-edit', 'product-delete',
            'transaction-view', 'transaction-create', 'transaction-cancel',
            'finance-view', 'finance-manage-payment', 'finance-crud-expense',
            'report-view-all',
            'adjustment-price', 'adjustment-stock',
        ]);

        // Berikan permission spesifik ke Staf
        $stafRole->givePermissionTo([
            'transaction-view', 'transaction-create', 'transaction-cancel',
        ]);
    }
}