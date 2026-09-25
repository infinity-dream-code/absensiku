<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Relative path — hindari generate URL absolut ke localhost
        // bila APP_URL / config cache salah di server.
        return $request->expectsJson() ? null : '/login';
    }
}
