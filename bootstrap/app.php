<?php

use App\Exceptions\LegacyRedirectHandler;
use App\Http\Middleware\CaptureAttribution;
use App\Http\Middleware\EnforceCanonicalHost;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            // The canonical-host 301 runs first so a request to a www or http
            // URL is redirected before any work is done.
            EnforceCanonicalHost::class,

            // Campaign parameters are captured on the landing request and
            // replayed onto later conversions.
            CaptureAttribution::class,
        ]);

        // The attribution cookie holds only campaign parameters and a random
        // visitor id — no secrets, nothing that identifies anyone off this
        // site. Leaving it unencrypted keeps it readable by the tracking
        // endpoint without a decrypt on every request.
        $middleware->encryptCookies(except: [
            CaptureAttribution::COOKIE,
            CaptureAttribution::VISITOR_COOKIE,
        ]);

        // navigator.sendBeacon cannot attach headers, so the tracking
        // endpoint can never present a CSRF token. Excluding it is safe and
        // necessary: the route only appends a row to an analytics log — it
        // changes no user state and performs no privileged action, so there
        // is nothing for a forged request to abuse beyond inflating a count,
        // which the rate limit and bot filter already bound.
        //
        // Note this is invisible to the test suite: Laravel skips CSRF
        // validation while running tests, so only a real request reveals it.
        // ConversionTrackingTest asserts the exclusion stays configured.
        $middleware->validateCsrfTokens(except: [
            't/click',
        ]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Laravel's default already returns JSON whenever the client asks for
        // it. The stock skeleton overrides that with an `api/*` path check,
        // which this application has no routes for — and which would make the
        // JSON tracking endpoint answer a validation failure with an HTML
        // redirect instead of a 422.

        // Legacy URL handling. Runs only when nothing else served the request
        // — see LegacyRedirectHandler for why this cannot be middleware.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return app(LegacyRedirectHandler::class)($request);
        });
    })->create();
