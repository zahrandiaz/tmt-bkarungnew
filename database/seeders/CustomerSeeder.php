<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create([
            'name' => 'Jono',
            'phone' => '081122334455',
            'address' => 'Jl. Raya Desa Sukamaju No. 10'
        ]);

        Customer::create([
            'name' => 'Pelanggan Umum',
            'phone' => '000000000000',
            'address' => '-'
        ]);
    }
}