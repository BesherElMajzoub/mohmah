<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Remembers how a visitor arrived, so a conversion can be credited to it.
 *
 * The problem this solves: someone clicks a Google Ad, lands on a service
 * page with ?gclid=…, reads for two minutes, navigates to the contact page,
 * and taps "اتصال". By then the gclid is long gone from the URL, and the
 * click looks like direct traffic. The office pays for the ad and cannot tell
 * it worked.
 *
 * So the campaign parameters are captured on the landing request and held in
 * a first-party cookie for the rest of the visit. ClickTrackingController
 * replays them onto every conversion.
 *
 * The cookie holds campaign parameters and a random visitor id — no personal
 * data, and nothing that identifies the visitor off this site.
 */
class CaptureAttribution
{
    public const COOKIE = 'attribution';

    public const VISITOR_COOKIE = 'visitor';

    /**
     * Matches the 90-day Google Ads conversion window, so a click can still
     * be attributed as long as Ads would credit it.
     */
    private const LIFETIME_MINUTES = 60 * 24 * 90;

    private const PARAMS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->hasCookie(self::VISITOR_COOKIE)) {
            $response->headers->setCookie($this->cookie(self::VISITOR_COOKIE, (string) Str::uuid()));
        }

        $incoming = $this->incomingParams($request);

        // Only overwrite when this request actually carries campaign
        // parameters. A later pageview with no parameters must not erase the
        // attribution captured on landing — that is the whole point.
        if ($incoming === []) {
            return $response;
        }

        $incoming['landing_path'] = $request->path();
        $incoming['referrer'] = Str::limit((string) $request->headers->get('referer'), 500, '');

        $response->headers->setCookie(
            $this->cookie(self::COOKIE, (string) json_encode($incoming, JSON_UNESCAPED_UNICODE))
        );

        return $response;
    }

    /**
     * @return array<string, string>
     */
    private function incomingParams(Request $request): array
    {
        $found = [];

        foreach (self::PARAMS as $param) {
            $value = $request->query($param);

            if (is_string($value) && trim($value) !== '') {
                // Bounded to keep a crafted URL from writing an oversized cookie.
                $found[$param] = Str::limit($value, 190, '');
            }
        }

        return $found;
    }

    private function cookie(string $name, string $value): Cookie
    {
        return new Cookie(
            name: $name,
            value: $value,
            expire: now()->addMinutes(self::LIFETIME_MINUTES)->getTimestamp(),
            path: '/',
            secure: request()->isSecure(),
            // Readable by JavaScript is not required — the server reads it on
            // the tracking request — so it stays HttpOnly.
            httpOnly: true,
            sameSite: Cookie::SAMESITE_LAX,
        );
    }
}
