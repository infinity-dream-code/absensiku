<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectNonAdminFromAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role === 'admin' || !$request->is('admin/*')) {
            return $next($request);
        }

        $user = auth()->user();

        if ($user->isPenilai() && $request->is('admin/kpi*')) {
            $targetPath = preg_replace('#^admin/#', '', $request->path());
            $query = $request->getQueryString();

            return redirect('/' . $targetPath . ($query ? '?' . $query : ''));
        }

        return redirect()
            ->route('attendance.index')
            ->with('error', 'Akses halaman admin ditolak. Gunakan menu Absensi atau Kelola KPI.');
    }
}
