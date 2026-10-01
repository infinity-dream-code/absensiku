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
        $host = strtolower($request->getHost());
        if ($host === '' || $this->isBlockedHost($host)) {
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

        $host = parse_url($location, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return;
        }

        $requestHost = strtolower($request->getHost());
        if (!$this->isBlockedHost($host) && strcasecmp($host, $requestHost) === 0) {
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
