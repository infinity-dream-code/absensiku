<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventLoginRedirectWhenAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isLoginRoute($request) && auth()->check()) {
            return $this->homeFor(auth()->user()->role === 'admin');
        }

        $response = $next($request);

        if (!auth()->check() || !$response instanceof RedirectResponse) {
            return $response;
        }

        if ($this->isLoginUrl($response->getTargetUrl())) {
            return $this->homeFor(auth()->user()->role === 'admin');
        }

        return $response;
    }

    private function isLoginRoute(Request $request): bool
    {
        return $request->is('login', 'admin/ict-login');
    }

    private function isLoginUrl(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $path = trim($path, '/');

        return in_array($path, ['login', 'admin/ict-login'], true);
    }

    private function homeFor(bool $isAdmin): RedirectResponse
    {
        return redirect()->route($isAdmin ? 'admin.dashboard' : 'attendance.index');
    }
}
