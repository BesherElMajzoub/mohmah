<?php

namespace Tests\Feature;

use App\Support\Settings;
use Database\Seeders\PageSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The map lives in the footer, which puts it on every page of the site.
 *
 * That placement is exactly why the deferred loading matters so much here: a
 * live Google Maps iframe in a global footer would make every single page pay
 * for its cookies and script bundle. The assertions below pin both halves —
 * that the map is genuinely everywhere, and that it contacts Google nowhere
 * until a visitor asks it to.
 */
class FooterMapTest extends TestCase
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
    public static function pages(): array
    {
        return [
            'homepage' => ['/'],
            'services index' => ['/الخدمات'],
            'service page' => ['/خدمات/التحكيم-التجاري'],
            'about' => ['/عن-المحامي-ريان-الجهني'],
            'contact' => ['/تواصل-معنا'],
            'blog index' => ['/المدونة'],
        ];
    }

    #[DataProvider('pages')]
    public function test_the_map_appears_in_the_footer_of_every_page(string $path): void
    {
        $this->get($path)
            ->assertOk()
            ->assertSee('عرض الخريطة');
    }

    /**
     * Google must not be contacted on load. The iframe sits inside an Alpine
     * <template>, which is inert markup rather than a live embed — no cookies,
     * no script bundle, and nothing competing with LCP.
     */
    #[DataProvider('pages')]
    public function test_google_is_not_contacted_until_the_map_is_requested(string $path): void
    {
        $html = $this->get($path)->getContent();

        $this->assertSame(
            1,
            substr_count($html, '<iframe'),
            "Unexpected iframe outside the click-to-load template on {$path}"
        );

        $this->assertMatchesRegularExpression(
            '/<template x-if="loaded">\s*<iframe/u',
            $html,
            'The map iframe must sit inside the deferred template.'
        );
    }

    /**
     * Until an address exists the map centres on the city, at city zoom, with
     * no pin implying a precise office location.
     */
    public function test_the_map_centres_on_the_city_when_no_address_is_set(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString(rawurlencode('جدة، المملكة العربية السعودية'), $html);
        $this->assertStringContainsString('&amp;z=11', $html);
    }

    public function test_the_map_centres_on_the_office_once_an_address_is_set(): void
    {
        app(Settings::class)->set('office_address', 'حي الروضة', 'string', 'contact');

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString(rawurlencode('حي الروضة، جدة'), $html);
        $this->assertStringContainsString('&amp;z=15', $html);
    }

    /**
     * The facade button is JavaScript-driven, so a plain link out to Google
     * Maps has to be present regardless.
     */
    public function test_a_plain_map_link_works_without_javascript(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('فتح الموقع في خرائط جوجل');
    }

    /**
     * The map must not also appear in the location section — one map per page.
     */
    public function test_the_map_is_not_duplicated_in_the_location_section(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertSame(1, substr_count($html, 'عرض الخريطة'));
    }
}
