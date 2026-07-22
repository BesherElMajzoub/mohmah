<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collapses every host and scheme variant onto the canonical one with a 301.
 *
 * Without this, http://, https://, www. and non-www all serve the same pages,
 * splitting link equity four ways and giving crawlers four URLs per page.
 * A canonical tag alone is a hint; the 301 is the instruction.
 *
 * Production only — local development runs on localhost and must not be
 * redirected to the live domain.
 */
class EnforceCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isProduction()) {
            return $next($request);
        }

        $canonical = rtrim((string) config('site.canonical_url'), '/');
        $canonicalHost = parse_url($canonical, PHP_URL_HOST);

        if (! $canonicalHost) {
            return $next($request);
        }

        $isCorrectHost = $request->getHost() === $canonicalHost;
        $isSecure = $request->isSecure();

        if ($isCorrectHost && $isSecure) {
            return $next($request);
        }

        // Preserve the path and query exactly — a redirect that drops the
        // query string would lose gclid and every UTM parameter, breaking
        // campaign attribution for anyone arriving on a www URL.
        $target = $canonical.$request->getRequestUri();

        return redirect()->away($target, 301);
    }
}
