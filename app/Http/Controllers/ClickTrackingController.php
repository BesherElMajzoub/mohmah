<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CaptureAttribution;
use App\Models\ClickEvent;
use App\Notifications\ClickAlert;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Records a call / WhatsApp / form conversion.
 *
 * Called via navigator.sendBeacon, which fires without delaying the tel: or
 * wa.me navigation. The response is 204 with no body because nothing reads
 * it — and because a beacon request cannot be handled by the page anyway.
 *
 * This endpoint is never the thing that makes a conversion work. The anchors
 * are real links; if this route is down, blocked, or JavaScript is off, the
 * visitor still reaches the phone.
 */
class ClickTrackingController extends Controller
{
    public function __construct(private readonly Settings $settings) {}

    public function __invoke(Request $request): Response
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', ClickEvent::TYPES)],
            'page_path' => ['required', 'string', 'max:512'],
            'page_type' => ['nullable', 'string', 'max:64'],
            'placement' => ['nullable', 'string', 'max:64'],
        ]);

        // Obvious crawlers would otherwise inflate the office's conversion
        // counts with traffic that never picked up a phone.
        if ($this->isBot($request->userAgent())) {
            return response()->noContent();
        }

        $event = ClickEvent::create([
            ...$validated,
            ...$this->attribution($request),
            'device' => $this->device($request->userAgent()),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'ip_hash' => $this->hashIp($request->ip()),
            'visitor_id' => $request->cookie(CaptureAttribution::VISITOR_COOKIE),
        ]);

        $this->notify($event);

        return response()->noContent();
    }

    /**
     * Replay the campaign parameters captured on the landing request.
     *
     * @return array<string, string|null>
     */
    private function attribution(Request $request): array
    {
        $raw = $request->cookie(CaptureAttribution::COOKIE);
        $data = is_string($raw) ? json_decode($raw, true) : null;
        $data = is_array($data) ? $data : [];

        $keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'landing_path', 'referrer'];

        $attribution = [];

        foreach ($keys as $key) {
            $value = $data[$key] ?? null;
            $attribution[$key] = is_string($value) && $value !== '' ? Str::limit($value, 500, '') : null;
        }

        // Fall back to this request's own referrer when the visitor arrived
        // with no campaign parameters at all.
        $attribution['referrer'] ??= Str::limit((string) $request->headers->get('referer'), 500, '') ?: null;

        return $attribution;
    }

    /**
     * HMAC rather than a plain hash: without the secret, a leaked table could
     * be brute-forced back to real IPs — the address space is small enough to
     * enumerate. Keyed with the app key, it cannot.
     */
    private function hashIp(?string $ip): ?string
    {
        if ($ip === null) {
            return null;
        }

        return hash_hmac('sha256', $ip, (string) config('app.key'));
    }

    /**
     * Device class from the user agent.
     *
     * Deliberately coarse — the dashboard only distinguishes phone, tablet
     * and desktop. iPhone is matched by name because not every iOS user
     * agent carries the "Mobi" token, and mobile share is the number the
     * office most needs to be right.
     */
    private function device(?string $userAgent): string
    {
        $ua = Str::lower((string) $userAgent);

        return match (true) {
            str_contains($ua, 'ipad') || str_contains($ua, 'tablet') => 'tablet',
            str_contains($ua, 'mobi')
                || str_contains($ua, 'iphone')
                || str_contains($ua, 'ipod')
                || str_contains($ua, 'android') => 'mobile',
            default => 'desktop',
        };
    }

    private function isBot(?string $userAgent): bool
    {
        $ua = Str::lower((string) $userAgent);

        if ($ua === '') {
            return true;
        }

        foreach (['bot', 'crawler', 'spider', 'headless', 'preview', 'lighthouse', 'pingdom', 'curl', 'wget'] as $needle) {
            if (str_contains($ua, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Instant email alert, off unless the client turns it on.
     *
     * The daily digest (SendClickDigest) is the default; this is for offices
     * that want to know the moment an enquiry arrives.
     */
    private function notify(ClickEvent $event): void
    {
        if (! $this->settings->get('alerts_instant_enabled', false)) {
            return;
        }

        $recipient = $this->settings->get('alerts_email');

        if (! $recipient) {
            return;
        }

        Notification::route('mail', $recipient)->notify(new ClickAlert($event));
    }
}
