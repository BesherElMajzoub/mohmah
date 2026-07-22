<?php

namespace Tests\Feature;

use App\Models\Redirect;
use App\Support\Url;
use Database\Seeders\PageSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SettingsSeeder::class, TaxonomySeeder::class, ServiceSeeder::class, PageSeeder::class]);
    }

    public function test_legacy_path_redirects_permanently(): void
    {
        Redirect::create([
            'from_path' => '/old-services.html',
            'to_path' => '/خدمات/محاماة-الشركات',
            'status_code' => 301,
        ]);

        $this->get('/old-services.html')
            ->assertStatus(301)
            ->assertRedirect(Url::canonical('/خدمات/محاماة-الشركات'));
    }

    /**
     * A legacy Arabic URL must match whether or not the crawler encoded it.
     */
    public function test_arabic_legacy_path_matches_encoded_or_decoded(): void
    {
        Redirect::create([
            'from_path' => '/خدماتنا/التحكيم',
            'to_path' => '/خدمات/التحكيم-التجاري',
            'status_code' => 301,
        ]);

        $this->get('/خدماتنا/التحكيم')->assertStatus(301);
        $this->get('/'.implode('/', array_map('rawurlencode', ['خدماتنا', 'التحكيم'])))->assertStatus(301);
    }

    public function test_trailing_slashes_are_normalised(): void
    {
        Redirect::create([
            'from_path' => '/legacy-page/',
            'to_path' => '/الخدمات',
            'status_code' => 301,
        ]);

        $this->get('/legacy-page')->assertStatus(301);
    }

    /**
     * 410 tells a crawler the URL is intentionally gone, so it stops
     * retrying — unlike a 404.
     */
    public function test_retired_url_returns_410(): void
    {
        Redirect::create([
            'from_path' => '/discontinued-service',
            'to_path' => null,
            'status_code' => 410,
        ]);

        $this->get('/discontinued-service')
            ->assertStatus(410)
            ->assertSee('لم تعد متاحة', false);
    }

    public function test_inactive_redirects_are_ignored(): void
    {
        Redirect::create([
            'from_path' => '/disabled-rule',
            'to_path' => '/الخدمات',
            'status_code' => 301,
            'is_active' => false,
        ]);

        $this->get('/disabled-rule')->assertNotFound();
    }

    /**
     * A real page must always win over a redirect rule, and the rule must
     * cost no query on the common path.
     */
    public function test_a_live_page_is_never_shadowed_by_a_redirect(): void
    {
        Redirect::create([
            'from_path' => '/الخدمات',
            'to_path' => '/',
            'status_code' => 301,
        ]);

        $this->get('/الخدمات')->assertOk();
    }

    public function test_hits_are_recorded_for_reporting(): void
    {
        $redirect = Redirect::create([
            'from_path' => '/tracked-legacy',
            'to_path' => '/الخدمات',
            'status_code' => 301,
        ]);

        $this->get('/tracked-legacy');
        $this->get('/tracked-legacy');

        $this->assertSame(2, $redirect->fresh()->hits);
        $this->assertNotNull($redirect->fresh()->last_hit_at);
    }

    public function test_unmatched_paths_still_404(): void
    {
        $this->get('/no-rule-for-this')->assertNotFound();
    }
}
