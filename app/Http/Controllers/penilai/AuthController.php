<?php

namespace App\Http\Controllers\Penilai;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PersistentLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->isPenilai()) {
            return redirect()->route('penilai.kpi.index');
        }
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('penilai.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where(function ($q) use ($request) {
            $q->where('email', $request->username)
                ->orWhere('nik', $request->username)
                ->orWhere('username', $request->username);
        })->first();

        if (
            $user
            && $user->isPenilai()
            && Hash::check($request->password, $user->password)
        ) {
            Auth::login($user, $request->filled('remember'));
            PersistentLogin::put($user);
            $request->session()->forget(['success', 'error', 'url.intended']);

            return redirect()->route('penilai.kpi.index')->with('success', 'Login penilai berhasil!');
        }

        return back()->withErrors([
            'username' => 'Akun bukan penilai atau kredensial tidak valid.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        PersistentLogin::forget();
        $request->session()->invalidate();
        $request->session()->flush();
        $request->session()->regenerateToken();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil!',
                'redirect' => route('penilai.login'),
            ]);
        }

        return redirect()->route('penilai.login')->with('success', 'Logout berhasil!');
    }
}
