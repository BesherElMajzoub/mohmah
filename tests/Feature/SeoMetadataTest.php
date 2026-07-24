<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use App\Support\Url;
use Database\Seeders\LawFirmInitialBlogPostsSeeder;
use Database\Seeders\PageSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SettingsSeeder::class, TaxonomySeeder::class, ServiceSeeder::class, PageSeeder::class]);
    }

    /**
     * @return array<int, string>
     */
    private function indexablePaths(): array
    {
        return [
            '/',
            '/الخدمات',
            '/عن-المحامي-ريان-الجهني',
            '/التراخيص-والاعتمادات',
            '/منهجية-العمل',
            '/تواصل-معنا',
            '/خدمات/محاماة-الشركات',
            '/خدمات/التحكيم-التجاري',
            '/خدمات/التوثيق',
            '/خدمات/التسجيل-العيني-للعقار',
        ];
    }

    /**
     * Duplicate titles are the most common cause of pages competing with each
     * other, so uniqueness is asserted across the whole indexable set rather
     * than page by page.
     */
    public function test_every_indexable_page_has_a_unique_title(): void
    {
        $titles = [];

        foreach ($this->indexablePaths() as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertSame(1, preg_match('/<title>(.*?)<\/title>/s', $html, $m), "No <title> on {$path}");

            $title = trim($m[1]);
            $this->assertNotEmpty($title, "Empty <title> on {$path}");

            $clash = $titles[$title] ?? null;
            $this->assertNull($clash, "Duplicate title on {$path} and {$clash}: \"{$title}\"");

            $titles[$title] = $path;
        }
    }

    public function test_every_indexable_page_has_a_unique_meta_description(): void
    {
        $descriptions = [];

        foreach ($this->indexablePaths() as $path) {
            $html = $this->get($path)->getContent();

            $this->assertSame(
                1,
                preg_match('/<meta name="description" content="(.*?)">/s', $html, $m),
                "No meta description on {$path}"
            );

            $description = trim($m[1]);

            $clash = $descriptions[$description] ?? null;
            $this->assertNull($clash, "Duplicate meta description on {$path} and {$clash}");

            $descriptions[$description] = $path;
        }
    }

    /**
     * The canonical must be the percent-encoded spelling on the canonical
     * host, byte-identical to what the sitemap emits.
     */
    public function test_canonical_matches_the_sitemap_spelling(): void
    {
        $service = Service::query()->published()->firstOrFail();

        $this->get($service->path())
            ->assertSee('<link rel="canonical" href="'.Url::canonical($service->path()).'">', false);
    }

    public function test_canonical_host_is_always_production(): void
    {
        $html = $this->get('/')->getContent();

        preg_match('/<link rel="canonical" href="(.*?)">/', $html, $m);

        $this->assertStringStartsWith('https://mohamah-ksa.com', $m[1]);
    }

    public function test_indexable_pages_declare_index_follow(): void
    {
        $this->get('/خدمات/محاماة-الشركات')
            ->assertSee('name="robots" content="index, follow', false);
    }

    public function test_legal_pages_are_noindex(): void
    {
        $this->get('/سياسة-الخصوصية')->assertSee('content="noindex, nofollow"', false);
        $this->get('/الشروط-والأحكام')->assertSee('content="noindex, nofollow"', false);
    }

    /**
     * A staging deploy must not be indexable no matter what the content says.
     */
    public function test_non_indexable_environment_forces_noindex_everywhere(): void
    {
        config()->set('site.indexable', false);

        foreach (['/', '/الخدمات', '/خدمات/محاماة-الشركات'] as $path) {
            $this->get($path)->assertSee('content="noindex, nofollow"', false);
        }
    }

    public function test_pages_emit_open_graph_and_twitter_cards(): void
    {
        $this->get('/')
            ->assertSee('property="og:title"', false)
            ->assertSee('property="og:url"', false)
            ->assertSee('property="og:image"', false)
            ->assertSee('property="og:locale" content="ar_SA"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false);
    }

    public function test_homepage_emits_organization_and_website_schema(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('"LegalService"', $html);
        $this->assertStringContainsString('"WebSite"', $html);
        $this->assertStringContainsString(config('site.phone_e164'), $html);
    }

    /**
     * Schema must not claim facts the client has not supplied. With address,
     * hours and socials empty, those properties must be absent entirely
     * rather than emitted blank.
     */
    public function test_schema_omits_unsupplied_facts(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('"address"', $html);
        $this->assertStringNotContainsString('"openingHours"', $html);
        $this->assertStringNotContainsString('"sameAs"', $html);

        // And never the markup that would invent credibility.
        $this->assertStringNotContainsString('aggregateRating', $html);
        $this->assertStringNotContainsString('"review"', $html);
        $this->assertStringNotContainsString('FAQPage', $html);
    }

    public function test_service_pages_emit_service_and_breadcrumb_schema(): void
    {
        $html = $this->get('/خدمات/التحكيم-التجاري')->getContent();

        $this->assertStringContainsString('"Service"', $html);
        $this->assertStringContainsString('"BreadcrumbList"', $html);
    }

    public function test_lawyer_page_emits_person_schema(): void
    {
        $this->get('/عن-المحامي-ريان-الجهني')
            ->assertSee('"Person"', false)
            ->assertSee(config('site.lawyer_name'), false);
    }

    public function test_robots_txt_references_the_sitemap_when_indexable(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Sitemap: '.Url::host().'/sitemap.xml', false)
            ->assertSee('Disallow: /admin', false);
    }

    public function test_robots_txt_blocks_everything_on_a_non_production_host(): void
    {
        config()->set('site.indexable', false);

        $this->get('/robots.txt')
            ->assertSee('Disallow: /', false)
            ->assertDontSee('Sitemap:', false);
    }

    public function test_pages_expose_breadcrumb_navigation(): void
    {
        $this->get('/خدمات/التوثيق')->assertSee('aria-label="مسار التصفح"', false);
    }

    public function test_admin_pages_are_noindex(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertSee('content="noindex, nofollow"', false);
    }

    public function test_seeded_page_titles_are_present(): void
    {
        $page = Page::byKey(Page::KEY_LICENSES);

        $this->get($page->path())->assertSee($page->h1, false);
    }

    /**
     * Publish the seeded articles so the blog index actually paginates.
     */
    private function seedPublishedPosts(): void
    {
        $this->seed(LawFirmInitialBlogPostsSeeder::class);

        Post::query()->update([
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }

    /**
     * The distinction that matters: page 2 must stay out of the index without
     * becoming a crawl dead end. `nofollow` here would leave every article
     * past the first page reachable only through the sitemap.
     */
    public function test_paginated_listings_are_noindex_but_still_followed(): void
    {
        $this->seedPublishedPosts();

        $this->get('/المدونة?page=2')
            ->assertOk()
            ->assertSee('content="noindex, follow"', false)
            ->assertDontSee('content="noindex, nofollow"', false);
    }

    public function test_paginated_listings_canonicalise_to_themselves(): void
    {
        $this->seedPublishedPosts();

        $this->get('/المدونة?page=2')
            ->assertSee('<link rel="canonical" href="'.Url::canonical('/المدونة').'?page=2">', false);
    }

    /**
     * A junk page parameter must not turn the indexable first page into a
     * noindex one — the paginator serves it as page 1, so the metadata has to
     * agree.
     */
    public function test_a_junk_page_parameter_leaves_the_first_page_indexable(): void
    {
        $this->seedPublishedPosts();

        foreach (['?page=0', '?page=-4', '?page=abc'] as $query) {
            $this->get('/المدونة'.$query)
                ->assertSee('content="index, follow', false);
        }
    }

    /**
     * An article published before anyone filled seo_description must still
     * ship a description rather than omitting the tag entirely.
     */
    public function test_meta_description_falls_back_to_the_excerpt(): void
    {
        $this->seedPublishedPosts();

        $post = Post::query()->published()->firstOrFail();
        $post->update(['seo_description' => null]);

        $this->get($post->path())
            ->assertOk()
            ->assertSee('<meta name="description" content="'.e($post->excerpt).'">', false);
    }

    /**
     * Articles the lawyer wrote must resolve to the same Person entity the
     * about page describes, not to a fresh anonymous node per article.
     */
    public function test_articles_credit_the_lawyer_as_one_entity(): void
    {
        $this->seedPublishedPosts();

        $post = Post::query()->published()->firstOrFail();
        $post->update(['author_name' => config('site.lawyer_name')]);

        $this->get($post->path())
            ->assertOk()
            ->assertSee(Url::host().'/#lawyer', false);
    }

    public function test_a_guest_author_does_not_borrow_the_lawyers_identity(): void
    {
        $this->seedPublishedPosts();

        $post = Post::query()->published()->firstOrFail();
        $post->update(['author_name' => 'كاتب ضيف']);

        $html = $this->get($post->path())->assertOk()->getContent();

        $this->assertStringContainsString('كاتب ضيف', $html);
        $this->assertStringNotContainsString('/#lawyer', $html);
    }
}
