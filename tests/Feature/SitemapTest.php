<?php

namespace Tests\Feature;

use App\Http\Controllers\SitemapController;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Service;
use App\Support\Url;
use Database\Seeders\PageSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The sitemap contract.
 *
 * Two properties matter and both are asserted here:
 *
 *   1. Publishing an article puts it in the sitemap immediately, with no
 *      second step. This is the behaviour the client asked for.
 *   2. The sitemap never lists a URL that would not return 200 — no drafts,
 *      no scheduled articles, no noindex pages.
 */
class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SettingsSeeder::class, TaxonomySeeder::class, ServiceSeeder::class, PageSeeder::class]);
    }

    public function test_sitemap_is_served_as_xml(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false);
    }

    /**
     * The headline requirement: publish an article, it is in the sitemap.
     */
    public function test_publishing_an_article_adds_it_to_the_sitemap_immediately(): void
    {
        // Warm the cache so the test proves invalidation, not a cold read.
        $this->get('/sitemap.xml')->assertOk();

        $post = Post::create([
            'post_category_id' => PostCategory::query()->value('id'),
            'slug' => 'اختيار-شرط-التحكيم-في-عقود-التوريد',
            'title' => 'اختيار شرط التحكيم في عقود التوريد',
            'body' => '<p>نص المقال.</p>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(Url::canonical($post->path()), false);
    }

    public function test_editing_an_article_refreshes_the_sitemap(): void
    {
        $post = Post::create([
            'post_category_id' => PostCategory::query()->value('id'),
            'slug' => 'مقال-قبل-التعديل',
            'title' => 'مقال قبل التعديل',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->get('/sitemap.xml')->assertSee(Url::canonical($post->path()), false);

        $post->update(['slug' => 'مقال-بعد-التعديل']);

        $response = $this->get('/sitemap.xml');
        $response->assertSee(Url::canonical('/المدونة/مقال-بعد-التعديل'), false);
        $response->assertDontSee(Url::canonical('/المدونة/مقال-قبل-التعديل'), false);
    }

    public function test_drafts_are_excluded(): void
    {
        $draft = Post::create([
            'post_category_id' => PostCategory::query()->value('id'),
            'slug' => 'مسودة-غير-منشورة',
            'title' => 'مسودة غير منشورة',
            'status' => Post::STATUS_DRAFT,
        ]);

        $this->get('/sitemap.xml')->assertDontSee(Url::canonical($draft->path()), false);
    }

    public function test_scheduled_articles_are_excluded_until_their_date(): void
    {
        $scheduled = Post::create([
            'post_category_id' => PostCategory::query()->value('id'),
            'slug' => 'مقال-مجدول',
            'title' => 'مقال مجدول',
            'status' => Post::STATUS_SCHEDULED,
            'published_at' => now()->addWeek(),
        ]);

        $this->get('/sitemap.xml')->assertDontSee(Url::canonical($scheduled->path()), false);

        // And the page itself 404s, so the sitemap and the site agree.
        $this->get($scheduled->path())->assertNotFound();

        $this->travel(8)->days();

        // Cache is time-bound too, so clear it the way a save would.
        Cache::forget(SitemapController::CACHE_KEY);

        $this->get('/sitemap.xml')->assertSee(Url::canonical($scheduled->path()), false);
        $this->get($scheduled->path())->assertOk();
    }

    public function test_noindex_pages_are_excluded(): void
    {
        // Privacy and terms are seeded noindex.
        $this->get('/sitemap.xml')
            ->assertDontSee(Url::canonical('/سياسة-الخصوصية'), false)
            ->assertDontSee(Url::canonical('/الشروط-والأحكام'), false);
    }

    public function test_draft_services_are_excluded(): void
    {
        $draft = Service::query()->where('status', Service::STATUS_DRAFT)->firstOrFail();

        $this->get('/sitemap.xml')->assertDontSee(Url::canonical($draft->path()), false);
    }

    public function test_published_services_are_included(): void
    {
        $service = Service::query()->published()->firstOrFail();

        $this->get('/sitemap.xml')->assertSee(Url::canonical($service->path()), false);
    }

    /**
     * The strongest guarantee available: fetch every URL the sitemap lists
     * and assert it actually returns 200.
     */
    public function test_every_listed_url_returns_200(): void
    {
        $xml = $this->get('/sitemap.xml')->getContent();

        preg_match_all('#<loc>(.*?)</loc>#', $xml, $matches);

        $this->assertNotEmpty($matches[1], 'Sitemap listed no URLs.');

        foreach ($matches[1] as $url) {
            $path = parse_url(html_entity_decode($url), PHP_URL_PATH);

            $this->get($path)->assertOk("Sitemap lists {$url} but it did not return 200.");
        }
    }
}
