<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->username)
            ->orWhere('nik', $request->username)
            ->first();

        if ($user && $user->role === 'admin' && Hash::check($request->password, $user->password)) {
            // Login dulu dengan token yang valid
            Auth::login($user, true);
            $request->session()->regenerate();

            $request->session()->forget(['success', 'error']);
            
            // Clear intended URL to prevent redirect to attendance page
            $request->session()->forget('url.intended');
            return redirect()->route('admin.dashboard')->with('success', 'Login berhasil!');
        }

        return back()->withErrors([
            'username' => 'Username atau password tidak valid.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        // Handle GET fallback untuk logout jika CSRF token expired
        if ($request->method() === 'GET' && $request->has('fallback')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin.login')->with('success', 'Logout berhasil!');
        }
        
        // Normal POST logout
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Return JSON jika AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil!',
                'redirect' => route('admin.login')
            ]);
        }
        
        return redirect()->route('admin.login')->with('success', 'Logout berhasil!');
    }
}
