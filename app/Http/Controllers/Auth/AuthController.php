<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     * Postcondition: mengembalikan view login
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Proses login user.
     * Precondition: login (username/email) dan password tersedia di request
     * Postcondition: jika valid → redirect ke dashboard; jika tidak → kembali dengan error
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required'],
        ]);

        // Determine if login is email or username
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginType => $request->login,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'login' => 'Username/Email atau password tidak valid.',
        ])->onlyInput('login');
    }

    /**
     * Logout user.
     * Postcondition: sesi dihapus, redirect ke halaman login
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
