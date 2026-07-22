<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the whole /admin prefix.
 *
 * 404 rather than 403 for a signed-in non-admin: a 403 confirms the admin
 * area exists at that path, and this site has no legitimate non-admin
 * accounts to distinguish.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->is_admin, 404);

        return $next($request);
    }
}
