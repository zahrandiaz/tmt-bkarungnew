<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::create([
            'name' => 'Karung Bandung',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 1, Bandung'
        ]);

        Supplier::create([
            'name' => 'Karung Cirebon',
            'phone' => '087654321098',
            'address' => 'Jl. Pesisir No. 2, Cirebon'
        ]);
    }
}