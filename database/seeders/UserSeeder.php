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
        // Membuat Pengguna Admin
        $admin = User::create([
            'name' => 'Admin TMT',
            'email' => 'admin@tmt.com',
            'password' => Hash::make('logikadunia24'),
        ]);
        $admin->assignRole('Admin');

        // Membuat Pengguna Manager
        $manager = User::create([
            'name' => 'Manager TMT',
            'email' => 'manager@tmt.com',
            'password' => Hash::make('manager'),
        ]);
        $manager->assignRole('Manager');

        // Membuat Pengguna Staf
        $staf = User::create([
            'name' => 'Staf TMT',
            'email' => 'staf@tmt.com',
            'password' => Hash::make('staf'),
        ]);
        $staf->assignRole('Staf');
    }
}