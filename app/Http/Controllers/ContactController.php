<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CaptureAttribution;
use App\Models\ClickEvent;
use App\Models\ContactSubmission;
use App\Support\Seo\SeoData;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contact page.
 *
 * Phone and WhatsApp are always present and always one tap away. The form is
 * secondary and off by default — nobody is made to complete a qualification
 * form before they can reach the office.
 *
 * Address, map, hours and email appear only once the client has entered real
 * values in settings.
 */
class ContactController extends Controller
{
    public function __construct(private readonly Settings $settings) {}

    public function show(): View
    {
        $breadcrumbs = [
            ['title' => 'الرئيسية', 'path' => '/'],
            ['title' => 'تواصل معنا', 'path' => null],
        ];

        $seo = (new SeoData(
            title: 'تواصل مع المكتب',
            description: 'تواصل مع مكتب المحامي ريان الجهني في جدة هاتفياً أو عبر واتساب لمناقشة احتياجك القانوني في محاماة الأعمال والتحكيم والتوثيق والعقارات.',
        ))->withBreadcrumbs($breadcrumbs);

        return view('pages.contact', [
            'seo' => $seo,
            'breadcrumbs' => $breadcrumbs,
            'formEnabled' => (bool) $this->settings->get('contact_form_enabled', false),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->settings->get('contact_form_enabled', false), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:180'],
            'subject' => ['nullable', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:4000'],
            // Honeypot. A real visitor never sees this field, so anything in
            // it is a bot. Cheaper and less hostile than a CAPTCHA, which
            // would put a puzzle between a serious client and the office.
            'website' => ['prohibited'],
        ], [
            'website.prohibited' => 'تعذّر إرسال النموذج.',
        ]);

        unset($validated['website']);

        $attribution = $this->attributionFrom($request);

        ContactSubmission::create([
            ...$validated,
            ...$attribution,
            'page_path' => $request->path(),
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
        ]);

        // Logged as a conversion alongside call and WhatsApp so the admin
        // dashboard reports one complete picture of enquiries.
        ClickEvent::create([
            'type' => ClickEvent::TYPE_CONTACT_FORM,
            'page_path' => $request->path(),
            'page_type' => 'contact',
            'placement' => 'contact_form',
            ...$attribution,
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
        ]);

        return redirect()
            ->route('contact')
            ->with('status', 'تم استلام رسالتك. سيتواصل معك المكتب في أقرب وقت.');
    }

    /**
     * Campaign parameters captured on the landing request.
     *
     * @return array<string, string|null>
     */
    private function attributionFrom(Request $request): array
    {
        $raw = $request->cookie(CaptureAttribution::COOKIE);
        $data = is_string($raw) ? json_decode($raw, true) : null;
        $data = is_array($data) ? $data : [];

        return [
            'utm_source' => $data['utm_source'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'gclid' => $data['gclid'] ?? null,
        ];
    }
}
