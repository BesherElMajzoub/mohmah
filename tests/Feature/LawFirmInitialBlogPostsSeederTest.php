<?php

namespace Tests\Feature;

use App\Models\Post;
use Database\Seeders\LawFirmInitialBlogPostsSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LawFirmInitialBlogPostsSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            TaxonomySeeder::class,
            ServiceSeeder::class,
        ]);
    }

    public function test_seeder_creates_exactly_twenty_posts(): void
    {
        $this->seed(LawFirmInitialBlogPostsSeeder::class);

        $this->assertSame(20, Post::query()->count());
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(LawFirmInitialBlogPostsSeeder::class);
        $this->seed(LawFirmInitialBlogPostsSeeder::class);

        $this->assertSame(20, Post::query()->count());
    }

    public function test_all_posts_are_drafts_and_not_published(): void
    {
        $this->seed(LawFirmInitialBlogPostsSeeder::class);

        $posts = Post::query()->get();

        $this->assertCount(20, $posts);
        foreach ($posts as $post) {
            $this->assertSame('draft', $post->status);
            $this->assertNull($post->published_at);
            $this->assertTrue($post->needs_review);
        }
    }

    public function test_all_posts_have_required_fields_and_stable_slugs(): void
    {
        $this->seed(LawFirmInitialBlogPostsSeeder::class);

        $posts = Post::query()->get();

        $this->assertCount(20, $posts);
        foreach ($posts as $post) {
            $this->assertNotEmpty($post->title, "Post ID {$post->id} missing title");
            $this->assertNotEmpty($post->slug, "Post ID {$post->id} missing slug");
            $this->assertNotEmpty($post->excerpt, "Post ID {$post->id} missing excerpt");
            $this->assertNotEmpty($post->body, "Post ID {$post->id} missing body");
            $this->assertNotNull($post->post_category_id, "Post ID {$post->id} missing category");
            $this->assertNotNull($post->category, "Post ID {$post->id} category relationship missing");
            $this->assertGreaterThan(0, $post->reading_minutes);
        }
    }

    public function test_all_posts_contain_legal_disclaimer(): void
    {
        $this->seed(LawFirmInitialBlogPostsSeeder::class);

        $posts = Post::query()->get();

        foreach ($posts as $post) {
            $this->assertStringContainsString(
                LawFirmInitialBlogPostsSeeder::DISCLAIMER,
                $post->body,
                "Post '{$post->title}' is missing the required legal disclaimer."
            );
        }
    }

    public function test_all_posts_have_focus_phrase_and_internal_service_links(): void
    {
        $this->seed(LawFirmInitialBlogPostsSeeder::class);

        $posts = Post::query()->get();

        foreach ($posts as $post) {
            $this->assertNotEmpty($post->focus_phrase, "Post '{$post->title}' missing focus phrase");
            $this->assertStringContainsString('/خدمات/', $post->body, "Post '{$post->title}' missing internal service link");
        }
    }

    public function test_high_priority_posts_exist_and_have_custom_conclusions(): void
    {
        $this->seed(LawFirmInitialBlogPostsSeeder::class);

        $highPrioritySlugs = [
            'قبل-دخول-شريك-جديد-ما-الاتفاقات-التي-تحمي-الشركة',
            'هل-تكفي-رسائل-واتساب-والبريد-الإلكتروني-لإثبات-حق-شركتك',
            'عقد-التطوير-العقاري-أين-تبدأ-المخاطر-قبل-أن-يبدأ-المشروع',
            'العقار-المملوك-على-الشيوع-كيف-تدار-القرارات-عند-اختلاف-الملاك',
        ];

        foreach ($highPrioritySlugs as $slug) {
            $post = Post::query()->where('slug', $slug)->first();
            $this->assertNotNull($post, "High priority post missing for slug {$slug}");
            $this->assertStringContainsString('class="article-conclusion"', $post->body);
            $this->assertStringNotContainsString('تواصل معنا', $post->body);
        }
    }

    public function test_related_services_are_attached_when_services_exist(): void
    {
        $this->seed(LawFirmInitialBlogPostsSeeder::class);

        $postWithService = Post::query()->where('slug', 'قبل-توقيع-عقد-تجاري-كبير-بنود-لا-ينبغي-أن-تتركها-للنموذج-الجاهز')->firstOrFail();

        $this->assertTrue($postWithService->services()->where('slug', 'صياغة-ومراجعة-العقود')->exists());
    }
}
