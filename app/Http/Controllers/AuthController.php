<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('attendance.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'password' => 'required|string',
        ]);

        // Login dengan username atau NIK
        $user = User::where('username', $request->nik)
            ->orWhere('nik', $request->nik)
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Prevent admin from logging in through employee login
            if ($user->role === 'admin') {
                return back()->withErrors([
                    'nik' => 'Silakan login melalui halaman admin.',
                ])->onlyInput('nik');
            }
            
            // Login dulu dengan token yang valid
            Auth::login($user, $request->filled('remember'));
            
            // JANGAN regenerate session/token setelah login karena akan menyebabkan
            // token di meta tag tidak match dengan token baru di session
            // Regenerate hanya saat logout untuk security
            
            // Clear any existing flash messages
            $request->session()->forget(['success', 'error']);

            // Jangan follow url.intended yang mengarah ke localhost / host asing
            // (sering tersimpan dari APP_URL salah atau session lama).
            $default = route('attendance.index');
            $intended = $request->session()->pull('url.intended');
            $target = $this->safeIntendedUrl($intended, $request) ?? $default;

            return redirect()->to($target)->with('success', 'Login berhasil!');
        }

        return back()->withErrors([
            'nik' => 'Username/NIK atau password tidak valid.',
        ])->onlyInput('nik');
    }

    public function logout(Request $request)
    {
        // Handle GET fallback untuk logout jika CSRF token expired
        if ($request->method() === 'GET' && $request->has('fallback')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('success', 'Logout berhasil!');
        }
        
        // Normal POST logout
        Auth::logout();
        
        // Invalidate dan flush session setelah logout
        $request->session()->invalidate();
        $request->session()->flush();
        
        // Regenerate token setelah invalidate untuk session baru
        $request->session()->regenerateToken();
        
        // Return JSON jika AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil!',
                'redirect' => route('login')
            ]);
        }
        
        return redirect()->route('login')->with('success', 'Logout berhasil!');
    }

    /**
     * Hanya izinkan redirect intended ke host aplikasi yang valid.
     */
    private function safeIntendedUrl(?string $intended, Request $request): ?string
    {
        if (!$intended) {
            return null;
        }

        $host = parse_url($intended, PHP_URL_HOST);
        if (!$host) {
            return null;
        }

        $blocked = ['localhost', '127.0.0.1', '::1'];
        if (in_array(strtolower($host), $blocked, true)) {
            return null;
        }

        $allowed = array_filter([
            parse_url((string) config('app.url'), PHP_URL_HOST),
            $request->getHost(),
        ]);

        if (!in_array($host, $allowed, true)) {
            return null;
        }

        return $intended;
    }
}
