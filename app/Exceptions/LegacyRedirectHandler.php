<?php

namespace App\Exceptions;

use App\Models\Redirect;
use App\Support\Url;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves the legacy redirect map when a URL matches no route.
 *
 * This lives in the exception handler rather than in middleware for a
 * structural reason: a URL that matches no route never enters the `web`
 * middleware group at all — routing resolves first, and an unmatched path
 * throws NotFoundHttpException before any route middleware runs. Middleware
 * would therefore only ever have seen 404s produced by abort() inside a
 * matched route, which is precisely the case that must NOT redirect.
 *
 * Consulting the table here means: a real page always wins, the query only
 * runs for URLs the application does not serve, and a legacy path is matched
 * whether the crawler requested it encoded or decoded.
 */
class LegacyRedirectHandler
{
    public function __invoke(Request $request): ?Response
    {
        // Never interfere with the admin, the tracking endpoint, or anything
        // that is not a plain page request.
        if (! $request->isMethod('GET') || $request->is('admin*', 'login', 't/*')) {
            return null;
        }

        $redirect = Redirect::query()
            ->where('from_path', Url::normalizePath($request->path()))
            ->where('is_active', true)
            ->first();

        if (! $redirect) {
            return null;
        }

        $redirect->recordHit();

        // 410 Gone: the URL existed and is intentionally retired. Unlike a
        // 404 this tells a crawler to stop retrying it.
        if ($redirect->isGone()) {
            return response()->view('errors.410', [], 410);
        }

        $destination = $redirect->destination();

        return $destination === null
            ? null
            : redirect()->away($destination, $redirect->status_code);
    }
}
