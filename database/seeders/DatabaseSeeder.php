<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Memanggil seeder peran dan pengguna
        $this->call([
            RoleSeeder::class,
            UserSeeder::class, // <-- TAMBAHKAN BARIS INI
        ]);
    }
}