<?php

namespace App\Http\Middleware;

use App\Support\PersistentLogin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestorePersistentLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('logout', 'admin/logout')) {
            return $next($request);
        }

        PersistentLogin::restore($request);

        return $next($request);
    }
}
