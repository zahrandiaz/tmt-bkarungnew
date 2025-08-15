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
        // Memanggil semua seeder yang dibutuhkan
        $this->call([
            // Seeder Inti
            RoleSeeder::class,
            PermissionSeeder::class, // [BARU V2.0.0] Daftarkan permission seeder
            UserSeeder::class, 
            
            // Seeder Data Master
            ProductCategorySeeder::class,
            ProductTypeSeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,
        ]);
    }
}