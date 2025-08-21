<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting; // [BARU] Import model Setting
use Illuminate\Support\Facades\Redirect; // [BARU] Import Redirect

class SettingsController extends Controller
{
    /**
     * Menampilkan halaman pengaturan.
     */
    public function index()
    {
        // Mengambil semua pengaturan dari database dan mengubahnya menjadi format key => value
        $settings = Setting::all()->pluck('value', 'key');
        
        return view('settings.index', compact('settings'));
    }

    /**
     * Memperbarui pengaturan di database.
     */
    public function update(Request $request)
    {
        // Validasi sederhana, bisa disesuaikan jika ada pengaturan lain
        $request->validate([
            'enable_automatic_stock' => 'nullable|string',
        ]);

        // Menggunakan updateOrCreate untuk membuat atau memperbarui pengaturan stok
        Setting::updateOrCreate(
            ['key' => 'enable_automatic_stock'],
            // Jika checkbox dicentang, value akan '1', jika tidak, value akan '0'
            ['value' => $request->has('enable_automatic_stock') ? '1' : '0']
        );

        return Redirect::route('settings.index')->with('status', 'pengaturan-berhasil-disimpan');
    }
}