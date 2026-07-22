<?php

namespace Tests\Feature;

use App\Support\Settings;
use Database\Seeders\PageSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The location section names the city, and nothing more, until real values
 * exist.
 *
 * The city is supplied and therefore safe to assert. The street address, map
 * link and working hours are not — and a plausible-looking placeholder for any
 * of them on a law office page is a factual claim a visitor may act on. These
 * tests pin both halves: nothing invented while the settings are empty, and
 * the real values surfacing the moment they are filled in.
 */
class LocationSectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SettingsSeeder::class, TaxonomySeeder::class, ServiceSeeder::class, PageSeeder::class]);
    }

    public function test_the_city_is_stated_on_the_homepage(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('موقع المكتب')
            ->assertSee('جدة — المملكة العربية السعودية');
    }

    public function test_the_city_is_stated_on_the_contact_page(): void
    {
        $this->get('/تواصل-معنا')
            ->assertOk()
            ->assertSee('موقع المكتب')
            ->assertSee('مقرّ المكتب في مدينة جدة');
    }

    /**
     * With no address, map or hours supplied, none of those blocks may appear
     * in any form.
     */
    public function test_nothing_is_invented_while_the_settings_are_empty(): void
    {
        foreach (['/', '/تواصل-معنا'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertDontSee('عنوان المكتب')
                ->assertDontSee('أوقات العمل')
                ->assertDontSee('عرض الموقع على خرائط جوجل');
        }
    }

    public function test_real_values_appear_once_they_are_entered(): void
    {
        $settings = app(Settings::class);
        $settings->set('office_address', 'حي الروضة، جدة', 'string', 'contact');
        $settings->set('office_hours', 'الأحد إلى الخميس، 9 ص — 5 م', 'string', 'contact');
        $settings->set('map_url', 'https://maps.app.goo.gl/example', 'string', 'contact');

        $this->get('/')
            ->assertOk()
            ->assertSee('حي الروضة، جدة')
            ->assertSee('الأحد إلى الخميس، 9 ص — 5 م')
            ->assertSee('عرض الموقع على خرائط جوجل')
            ->assertSee('https://maps.app.goo.gl/example', false);
    }

    /**
     * The contact page leads with its own phone and WhatsApp cards, so the
     * location section there must not repeat them.
     */
    public function test_the_contact_page_does_not_duplicate_the_contact_card(): void
    {
        $this->get('/تواصل-معنا')
            ->assertOk()
            ->assertDontSee('للتواصل المباشر');
    }
}
