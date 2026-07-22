<?php

namespace Tests\Feature;

use App\Http\Middleware\CaptureAttribution;
use App\Models\ClickEvent;
use Database\Seeders\PageSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The conversion path.
 *
 * The most important assertions here are the ones proving that tracking is
 * never in the way: the phone and WhatsApp links are real anchors with
 * correct hrefs, present on every page, and reachable without JavaScript.
 */
class ConversionTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SettingsSeeder::class, TaxonomySeeder::class, ServiceSeeder::class, PageSeeder::class]);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function pagesWithCtas(): array
    {
        return [
            'homepage' => ['/'],
            'services index' => ['/الخدمات'],
            'service page' => ['/خدمات/محاماة-الشركات'],
            'about' => ['/عن-المحامي-ريان-الجهني'],
            'contact' => ['/تواصل-معنا'],
        ];
    }

    #[DataProvider('pagesWithCtas')]
    public function test_call_and_whatsapp_links_are_correct_on_every_page(string $path): void
    {
        $this->get($path)
            ->assertOk()
            ->assertSee('href="tel:+966536100125"', false)
            ->assertSee('href="https://wa.me/966536100125"', false);
    }

    public function test_displayed_phone_number_is_the_local_format(): void
    {
        $this->get('/تواصل-معنا')->assertSee('0536100125', false);
    }

    /**
     * The floating actions are always-available conversion surface, present
     * on every page at every breakpoint.
     */
    #[DataProvider('pagesWithCtas')]
    public function test_floating_actions_offer_both_actions(string $path): void
    {
        $html = $this->get($path)->getContent();

        // Exactly one of each — a duplicated floating button would stack.
        $this->assertSame(
            2,
            substr_count($html, 'data-placement="floating"'),
            "Expected exactly one floating call and one floating WhatsApp button on {$path}"
        );

        $this->assertStringContainsString('اتصل بالمكتب', $html);
        $this->assertStringContainsString('راسلنا عبر واتساب', $html);
    }

    /**
     * The floating buttons must clear the iOS home indicator rather than
     * sitting underneath it.
     */
    public function test_floating_actions_respect_the_safe_area_inset(): void
    {
        $this->get('/')->assertSee('env(safe-area-inset-bottom)', false);
    }

    /**
     * Tracking is an observer on real links, not a wrapper around them. If
     * this ever became a JS-driven handler, the anchors would lose their
     * hrefs and this assertion would fail.
     */
    public function test_cta_links_work_without_javascript(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('onclick=', $html);
        $this->assertStringContainsString('data-track="call"', $html);
        $this->assertStringContainsString('data-track="whatsapp"', $html);
    }

    public function test_no_free_consultation_wording_anywhere(): void
    {
        foreach (array_column(self::pagesWithCtas(), 0) as $path) {
            $this->get($path)->assertDontSee('استشارة مجانية');
        }
    }

    // --- The tracking endpoint --------------------------------------------

    public function test_click_is_recorded(): void
    {
        $this->postJson('/t/click', [
            'type' => 'call',
            'page_path' => '/خدمات/محاماة-الشركات',
            'page_type' => 'service',
            'placement' => 'hero',
        ], ['User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)'])
            ->assertNoContent();

        $event = ClickEvent::query()->sole();

        $this->assertSame('call', $event->type);
        $this->assertSame('/خدمات/محاماة-الشركات', $event->page_path);
        $this->assertSame('hero', $event->placement);
        $this->assertSame('mobile', $event->device);
    }

    /**
     * The tracking endpoint must be exempt from CSRF verification.
     *
     * navigator.sendBeacon cannot attach headers, so a real browser can never
     * present a token — without the exemption every beacon is rejected with a
     * 419 and no conversion is ever recorded.
     *
     * This is asserted against the configured exception list rather than by
     * making a request, because Laravel skips CSRF validation entirely while
     * running tests: a normal request-based test passes either way and would
     * not have caught this.
     */
    public function test_tracking_endpoint_is_exempt_from_csrf(): void
    {
        $middleware = app(ValidateCsrfToken::class);

        $this->assertContains(
            't/click',
            $middleware->getExcludedPaths(),
            'The tracking endpoint must stay in the CSRF exception list or sendBeacon will always 419.'
        );
    }

    public function test_invalid_click_type_is_rejected(): void
    {
        $this->postJson('/t/click', [
            'type' => 'not-a-real-type',
            'page_path' => '/',
        ], ['User-Agent' => 'Mozilla/5.0'])->assertUnprocessable();

        $this->assertSame(0, ClickEvent::query()->count());
    }

    public function test_bot_traffic_is_not_counted(): void
    {
        $this->postJson('/t/click', [
            'type' => 'call',
            'page_path' => '/',
        ], ['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])
            ->assertNoContent();

        $this->assertSame(0, ClickEvent::query()->count());
    }

    /**
     * The raw IP must never be stored.
     */
    public function test_ip_address_is_hashed_not_stored(): void
    {
        $this->postJson('/t/click', [
            'type' => 'whatsapp',
            'page_path' => '/',
        ], ['User-Agent' => 'Mozilla/5.0'])->assertNoContent();

        $event = ClickEvent::query()->sole();

        $this->assertNotNull($event->ip_hash);
        $this->assertSame(64, strlen($event->ip_hash));
        $this->assertStringNotContainsString('127.0.0.1', $event->ip_hash);
    }

    public function test_endpoint_is_rate_limited(): void
    {
        // The limit is 30/minute; the 31st must be rejected.
        for ($i = 0; $i < 30; $i++) {
            $this->postJson('/t/click', [
                'type' => 'call',
                'page_path' => '/',
            ], ['User-Agent' => 'Mozilla/5.0'])->assertNoContent();
        }

        $this->postJson('/t/click', [
            'type' => 'call',
            'page_path' => '/',
        ], ['User-Agent' => 'Mozilla/5.0'])->assertStatus(429);
    }

    // --- Attribution -------------------------------------------------------

    /**
     * The scenario this whole mechanism exists for: someone arrives from a
     * Google Ad, browses to another page, and only then taps call. The
     * conversion must still be credited to the ad.
     */
    public function test_campaign_attribution_survives_navigation(): void
    {
        $landing = $this->get('/خدمات/التحكيم-التجاري?gclid=TEST-GCLID-123&utm_source=google&utm_campaign=arbitration');

        $cookie = $landing->getCookie(CaptureAttribution::COOKIE, false);
        $this->assertNotNull($cookie, 'Attribution cookie was not set on landing.');

        // A later conversion, from a page carrying no campaign parameters.
        // withCredentials() is required because Laravel's JSON test helpers
        // omit cookies otherwise; a real same-origin sendBeacon sends them.
        $this->withCredentials()
            ->withUnencryptedCookie(CaptureAttribution::COOKIE, $cookie->getValue())
            ->postJson('/t/click', [
                'type' => 'call',
                'page_path' => '/تواصل-معنا',
            ], ['User-Agent' => 'Mozilla/5.0'])
            ->assertNoContent();

        $event = ClickEvent::query()->sole();

        $this->assertSame('TEST-GCLID-123', $event->gclid);
        $this->assertSame('google', $event->utm_source);
        $this->assertSame('arbitration', $event->utm_campaign);
        $this->assertSame('إعلانات جوجل', $event->sourceLabel());
    }

    /**
     * A later pageview without parameters must not erase the attribution
     * captured on landing.
     */
    public function test_later_pageviews_do_not_overwrite_attribution(): void
    {
        $landing = $this->get('/?utm_source=newsletter');
        $original = $landing->getCookie(CaptureAttribution::COOKIE, false)->getValue();

        $second = $this->withUnencryptedCookie(CaptureAttribution::COOKIE, $original)->get('/الخدمات');

        $this->assertNull(
            $second->getCookie(CaptureAttribution::COOKIE, false),
            'A pageview with no campaign parameters overwrote the stored attribution.'
        );
    }

    public function test_analytics_scripts_are_absent_when_no_measurement_id_is_configured(): void
    {
        config()->set('site.ga4_id', null);
        config()->set('site.google_ads_id', null);

        $this->get('/')->assertDontSee('googletagmanager.com', false);
    }
}
