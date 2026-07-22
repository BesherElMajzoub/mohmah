<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Support\Url;
use Database\Seeders\PageSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SettingsSeeder::class, TaxonomySeeder::class, ServiceSeeder::class, PageSeeder::class]);

        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    // --- Access control ----------------------------------------------------

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/services')->assertRedirect('/login');
        $this->get('/admin/settings')->assertRedirect('/login');
    }

    /**
     * A signed-in non-admin gets a 404, not a 403 — a 403 would confirm the
     * admin area exists at that path.
     */
    public function test_non_admin_users_get_a_404(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertNotFound();
    }

    public function test_admin_can_sign_in(): void
    {
        $user = User::factory()->create([
            'email' => 'lawyer@example.test',
            'password' => bcrypt('correct-horse-battery'),
            'is_admin' => true,
        ]);

        $this->post('/login', [
            'email' => 'lawyer@example.test',
            'password' => 'correct-horse-battery',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_credentials_are_rejected(): void
    {
        $this->post('/login', [
            'email' => 'nobody@example.test',
            'password' => 'wrong',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    // --- Screens render ----------------------------------------------------

    /**
     * @return array<string, array{string}>
     */
    public static function adminScreens(): array
    {
        return [
            'dashboard' => ['/admin'],
            'services' => ['/admin/services'],
            'new service' => ['/admin/services/create'],
            'service categories' => ['/admin/service-categories'],
            'posts' => ['/admin/posts'],
            'new post' => ['/admin/posts/create'],
            'post categories' => ['/admin/post-categories'],
            'pages' => ['/admin/pages'],
            'media' => ['/admin/media'],
            'redirects' => ['/admin/redirects'],
            'new redirect' => ['/admin/redirects/create'],
            'settings' => ['/admin/settings'],
            'click analytics' => ['/admin/clicks'],
            'submissions' => ['/admin/submissions'],
        ];
    }

    #[DataProvider('adminScreens')]
    public function test_admin_screens_render(string $path): void
    {
        $this->actingAs($this->admin)->get($path)->assertOk();
    }

    public function test_edit_screens_render(): void
    {
        $service = Service::query()->firstOrFail();
        $category = ServiceCategory::query()->firstOrFail();
        $page = Page::query()->firstOrFail();

        $this->actingAs($this->admin)->get("/admin/services/{$service->id}/edit")->assertOk();
        $this->actingAs($this->admin)->get("/admin/service-categories/{$category->id}/edit")->assertOk();
        $this->actingAs($this->admin)->get("/admin/pages/{$page->id}/edit")->assertOk();
    }

    // --- Publishing --------------------------------------------------------

    /**
     * The full publishing workflow: create an article in the admin, and it is
     * live and in the sitemap with no further action.
     */
    public function test_creating_a_published_article_makes_it_live_and_indexed(): void
    {
        $this->actingAs($this->admin)->post('/admin/posts', [
            'post_category_id' => PostCategory::query()->value('id'),
            'title' => 'ما الذي يجعل شرط التحكيم قابلاً للتنفيذ',
            'excerpt' => 'مراجعة عملية لصياغة شرط التحكيم في العقود التجارية.',
            'body' => '<h2>الصياغة</h2><p>نص تجريبي كافٍ لحساب مدة القراءة.</p>',
            'status' => Post::STATUS_PUBLISHED,
            'is_indexable' => '1',
            'author_name' => 'ريان الجهني',
        ])->assertRedirect();

        $post = Post::query()->sole();

        // Slug derived from the Arabic title, with the tanween on "قابلاً"
        // normalised away so the URL is typeable.
        $this->assertSame('ما-الذي-يجعل-شرط-التحكيم-قابلا-للتنفيذ', $post->slug);

        // Reading time computed from the body.
        $this->assertNotNull($post->reading_minutes);

        // Live.
        $this->get($post->path())->assertOk()->assertSee($post->title, false);

        // And in the sitemap.
        $this->get('/sitemap.xml')->assertSee(Url::canonical($post->path()), false);
    }

    public function test_scheduled_article_requires_a_date(): void
    {
        $this->actingAs($this->admin)->post('/admin/posts', [
            'post_category_id' => PostCategory::query()->value('id'),
            'title' => 'مقال بلا تاريخ',
            'status' => Post::STATUS_SCHEDULED,
        ])->assertSessionHasErrors('published_at');
    }

    public function test_editing_a_service_updates_the_public_page(): void
    {
        $service = Service::query()->published()->firstOrFail();

        $this->actingAs($this->admin)->put("/admin/services/{$service->id}", [
            'service_category_id' => $service->service_category_id,
            'title' => $service->title,
            'h1' => 'عنوان محدَّث للخدمة',
            'summary' => 'ملخص محدَّث.',
            'status' => Service::STATUS_PUBLISHED,
            'is_indexable' => '1',
        ])->assertRedirect();

        $this->get($service->fresh()->path())->assertSee('عنوان محدَّث للخدمة', false);
    }

    /**
     * Empty repeater rows must not persist — they would render as empty
     * bullets on the public page.
     */
    public function test_blank_repeater_rows_are_discarded(): void
    {
        $service = Service::query()->firstOrFail();

        $this->actingAs($this->admin)->put("/admin/services/{$service->id}", [
            'service_category_id' => $service->service_category_id,
            'title' => $service->title,
            'h1' => $service->h1,
            'status' => Service::STATUS_DRAFT,
            'audience' => ['فئة حقيقية', '', '   '],
            'scope' => ['', ''],
            'faqs' => [
                ['question' => 'سؤال', 'answer' => 'إجابة'],
                ['question' => '', 'answer' => ''],
            ],
        ])->assertRedirect();

        $service->refresh();

        $this->assertSame(['فئة حقيقية'], $service->audience);
        $this->assertSame([], $service->scope);
        $this->assertCount(1, $service->faqs);
    }

    public function test_settings_can_be_saved_and_appear_on_the_site(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings', [
            'office_email' => 'info@mohamah-ksa.com',
            'office_address' => 'جدة، حي الروضة',
            'office_hours' => 'الأحد إلى الخميس، 9 صباحاً — 5 مساءً',
        ])->assertRedirect();

        $this->get('/تواصل-معنا')
            ->assertSee('info@mohamah-ksa.com', false)
            ->assertSee('جدة، حي الروضة', false);
    }

    /**
     * The inverse, and the more important half: an unset setting hides its
     * section rather than rendering a placeholder.
     */
    public function test_unset_settings_hide_their_section_entirely(): void
    {
        $this->get('/تواصل-معنا')
            ->assertDontSee('عنوان المكتب', false)
            ->assertDontSee('أوقات العمل', false);
    }

    public function test_category_with_services_cannot_be_deleted(): void
    {
        $category = ServiceCategory::query()->has('services')->firstOrFail();

        $this->actingAs($this->admin)
            ->delete("/admin/service-categories/{$category->id}")
            ->assertSessionHasErrors('category');

        $this->assertModelExists($category);
    }

    public function test_click_analytics_csv_export_downloads(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/clicks/export')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
