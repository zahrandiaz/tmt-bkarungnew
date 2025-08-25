<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Pengaturan Stok
            ['key' => 'enable_automatic_stock', 'value' => '1'], // 1 = Aktif, 0 = Nonaktif
            
            // [BARU] Pengaturan Informasi Toko
            ['key' => 'store_name', 'value' => 'TMT Bagja Karung'],
            ['key' => 'store_address', 'value' => 'Jl. Raya Cimalaka No. 123, Sumedang'],
            ['key' => 'store_phone', 'value' => '081234567890'],
            ['key' => 'invoice_footer_notes', 'value' => 'Terima kasih telah berbelanja di TMT Bagja Karung.'],
        ];

        // Menggunakan loop untuk membuat atau memperbarui setiap pengaturan
        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}