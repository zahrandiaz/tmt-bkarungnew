<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest; // [UBAH]
use App\Models\Setting;
use Illuminate\Http\Request; // Hapus jika tidak ada method lain yg butuh
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * Menampilkan halaman pengaturan.
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings.index', compact('settings'));
    }

    /**
     * Memperbarui pengaturan di database.
     */
    // [UBAH] Gunakan UpdateSettingsRequest
    public function update(UpdateSettingsRequest $request)
    {
        // Validasi sudah terjadi secara otomatis
        $validated = $request->validated();
        
        // Tambahkan kembali nilai checkbox jika tidak ada
        $validated['enable_automatic_stock'] = $request->has('enable_automatic_stock') ? '1' : '0';

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Hapus cache pengaturan agar perubahan langsung terlihat.
        Cache::forget('app_settings');

        // [UBAH] Standarkan pesan notifikasi
        return Redirect::route('settings.index')->with('success', 'Pengaturan berhasil diperbarui.');
    }
}