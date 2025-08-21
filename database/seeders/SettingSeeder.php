<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting; // [BARU] Import model Setting

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // [BARU] Membuat atau memperbarui pengaturan stok otomatis
        Setting::updateOrCreate(
            ['key' => 'enable_automatic_stock'],
            ['value' => '1'] // 1 = Aktif, 0 = Nonaktif
        );
    }
}