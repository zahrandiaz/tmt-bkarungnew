<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Cache; // [BARU] Import Cache facade

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
    public function update(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_address' => 'required|string|max:500',
            'store_phone' => 'required|string|max:20',
            'invoice_footer_notes' => 'required|string|max:500',
            'enable_automatic_stock' => 'nullable|string',
        ]);

        $inputs = $request->except('_token');
        $inputs['enable_automatic_stock'] = $request->has('enable_automatic_stock') ? '1' : '0';

        foreach ($inputs as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // [PERBAIKAN BUG] Hapus cache pengaturan agar perubahan langsung terlihat.
        Cache::forget('app_settings');

        return Redirect::route('settings.index')->with('status', 'pengaturan-berhasil-disimpan');
    }
}