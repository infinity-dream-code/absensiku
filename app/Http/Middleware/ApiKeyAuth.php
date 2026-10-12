<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ApiKeyAuth
{
    /**
     * Handle an incoming request.
     * Accepts either static API key OR JWT token
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide Authorization header with Bearer token or API key.'
            ], 401);
        }

        // Remove "Bearer " prefix if present
        $token = $authHeader;
        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        }

        $staticApiKey = config('services.api.static_key');

        // Check if token matches static API key
        if (is_string($staticApiKey) && $staticApiKey !== '' && hash_equals($staticApiKey, $token)) {
            // Static API key is valid, allow request to proceed
            // Store in request for later use
            $request->merge(['_api_key_authenticated' => true]);
            return $next($request);
        }

        // If not static key, try JWT token
        try {
            JWTAuth::setToken($token);
            $user = JWTAuth::authenticate();
            if ($user) {
                // JWT token is valid, allow request to proceed
                return $next($request);
            }
        } catch (JWTException $e) {
            // JWT token is invalid, return error
        }

        // Neither static key nor JWT token is valid
        return response()->json([
            'success' => false,
            'message' => 'Invalid API key or JWT token. Please provide a valid static API key or JWT token.'
        ], 401);
    }
}
