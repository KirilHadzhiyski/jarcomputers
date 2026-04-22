<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalHostRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalHost = strtolower(trim((string) config('communications.domain.canonical_host')));
        $redirectHosts = collect(config('communications.domain.redirect_hosts', []))
            ->map(fn (mixed $host): string => strtolower(trim((string) $host)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $requestHost = strtolower($request->getHost());
        $forceHttps = (bool) config('communications.domain.force_https');
        $shouldRedirectHost = filled($canonicalHost)
            && $requestHost !== $canonicalHost
            && in_array($requestHost, $redirectHosts, true);
        $shouldRedirectScheme = $forceHttps && ! $request->isSecure();

        if (! $shouldRedirectHost && ! $shouldRedirectScheme) {
            return $next($request);
        }

        $targetHost = $canonicalHost ?: $requestHost;
        $targetScheme = $forceHttps
            ? 'https'
            : (parse_url((string) config('communications.domain.app_url'), PHP_URL_SCHEME) ?: $request->getScheme());
        $statusCode = in_array($request->getMethod(), ['GET', 'HEAD'], true) ? 301 : 308;
        $targetUrl = "{$targetScheme}://{$targetHost}{$request->getRequestUri()}";

        return redirect()->away($targetUrl, $statusCode);
    }
}
