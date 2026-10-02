<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class UsePublicUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $this->bindUrlToRequest($request);

        $response = $next($request);

        if ($response instanceof RedirectResponse) {
            $this->keepRedirectOnSameHost($request, $response);
        }

        return $response;
    }

    private function bindUrlToRequest(Request $request): void
    {
        $requestHost = strtolower($request->getHost());
        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $appScheme = parse_url((string) config('app.url'), PHP_URL_SCHEME);

        if ($this->isBlockedHost($requestHost) && $appHost !== '' && !$this->isBlockedHost($appHost)) {
            $scheme = is_string($appScheme) && $appScheme !== '' ? $appScheme : 'https';
            URL::forceRootUrl($scheme.'://'.$appHost);
            URL::forceScheme($scheme);

            return;
        }

        if ($requestHost === '' || $this->isBlockedHost($requestHost)) {
            return;
        }

        URL::forceRootUrl($request->getSchemeAndHttpHost());
        URL::forceScheme($request->getScheme());
    }

    private function keepRedirectOnSameHost(Request $request, RedirectResponse $response): void
    {
        $location = $response->headers->get('Location');
        if (!is_string($location) || !preg_match('#^https?://#i', $location)) {
            return;
        }

        $path = parse_url($location, PHP_URL_PATH) ?: '/';
        $query = parse_url($location, PHP_URL_QUERY);
        $response->setTargetUrl($path . ($query ? '?' . $query : ''));
    }

    private function isBlockedHost(string $host): bool
    {
        return in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true);
    }
}
