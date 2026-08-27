<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Fungsi untuk papar borang login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Fungsi untuk proses data login (Menggantikan auth_login.php)
    public function authenticate(Request $request)
    {
        // 1. Validasi input
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // 2. Semak pangkalan data dan log masuk (Auth::attempt)
        if (Auth::attempt($credentials)) {
            // Jika berjaya, jana semula session untuk elak 'Session Fixation'
            $request->session()->regenerate();

            // Bawa pengguna ke dashboard
            return redirect()->intended('/');
        }

        // 3. Jika gagal, patah balik ke halaman login berserta ralat
        return back()->withErrors([
            'username' => 'Username atau kata laluan tidak sah.',
        ]);
    }

    // Fungsi untuk log keluar (Menggantikan logout.php)
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}