<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // [MODIFIKASI V2.0.0] Gunakan firstOrCreate untuk mencegah eror duplikat
        // Cari pengguna berdasarkan email, jika tidak ada, buat baru dengan data yang disediakan.
        
        // Membuat Pengguna Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@tmt.com'],
            [
                'name' => 'Admin TMT',
                'password' => Hash::make('logikadunia24'),
            ]
        );
        $admin->assignRole('Admin');

        // Membuat Pengguna Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@tmt.com'],
            [
                'name' => 'Manager TMT',
                'password' => Hash::make('manager'),
            ]
        );
        $manager->assignRole('Manager');

        // Membuat Pengguna Staf
        $staf = User::firstOrCreate(
            ['email' => 'staf@tmt.com'],
            [
                'name' => 'Staf TMT',
                'password' => Hash::make('staf'),
            ]
        );
        $staf->assignRole('Staf');
    }
}