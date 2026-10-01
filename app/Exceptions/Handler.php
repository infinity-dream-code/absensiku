<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenMismatchException) {
            return $this->recoverTokenMismatch($request);
        }

        if ($e instanceof PostTooLargeException && ($request->expectsJson() || $request->ajax())) {
            return response()->json([
                'success' => false,
                'message' => 'Foto terlalu besar. Ambil ulang foto lalu coba check-in lagi.',
            ], 422);
        }

        if ($this->isTransientFailure($e) && $request->isMethod('GET') && !$request->expectsJson() && !$request->ajax()) {
            if ($request->cookie('absensi_500_retry') !== '1') {
                return redirect()->to($request->fullUrl())
                    ->withCookie(cookie('absensi_500_retry', '1', 1));
            }
        }

        return parent::render($request, $e);
    }

    private function recoverTokenMismatch(Request $request): Response
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'CSRF token mismatch.',
            ], 419);
        }

        $back = $request->headers->get('referer');
        $target = '/admin';
        if (is_string($back) && $back !== '') {
            $host = parse_url($back, PHP_URL_HOST);
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            if (is_string($host) && ($host === $request->getHost() || $host === $appHost)) {
                $target = $back;
            }
        }

        return redirect()->to($target);
    }

    private function isTransientFailure(Throwable $e): bool
    {
        $blob = '';
        $current = $e;
        while ($current) {
            $blob .= ' ' . $current->getMessage();
            $current = $current->getPrevious();
        }

        $blob = strtolower($blob);
        foreach ([
            'deadlock',
            'lock wait timeout',
            'gone away',
            'lost connection',
            'temporarily unavailable',
            'unable to create lock',
        ] as $needle) {
            if (str_contains($blob, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Return JSON response for API routes
        if ($request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide a valid token.'
            ], 401);
        }

        // Relative path agar tidak pernah redirect ke http://localhost/...
        return redirect()->guest('/login');
    }
}
