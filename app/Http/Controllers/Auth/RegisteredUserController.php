<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
// [UBAH] Impor class RegisterRequest yang baru
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
// [HAPUS] Request tidak digunakan lagi secara langsung
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    // [UBAH] Ganti tipe request dari Request menjadi RegisterRequest
    public function store(RegisterRequest $request): RedirectResponse
    {
        // [HAPUS] Blok validasi ini sudah tidak diperlukan lagi.
        // Validasi sekarang ditangani secara otomatis oleh RegisterRequest.
        // $request->validate([...]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}