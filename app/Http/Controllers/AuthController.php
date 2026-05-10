<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'super_admin') {
                return redirect()->intended('/super-admin/dashboard');
            } elseif (Auth::user()->role === 'asatidz') {
                return redirect()->intended('/dashboard');
            } elseif (Auth::user()->role === 'santri') {
                return redirect()->intended('/santri/dashboard');
            }
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Role-based redirection
            if (Auth::user()->role === 'super_admin') {
                return redirect()->intended('/super-admin/dashboard');
            } elseif (Auth::user()->role === 'asatidz') {
                return redirect()->intended('/dashboard');
            } elseif (Auth::user()->role === 'santri') {
                return redirect()->intended('/santri/dashboard');
            }
            
            return redirect('/');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
