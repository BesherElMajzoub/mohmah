<?php

namespace Tests\Feature;

use App\Models\Service;
use Database\Seeders\PageSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
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
    public static function publicPaths(): array
    {
        return [
            'homepage' => ['/'],
            'services index' => ['/الخدمات'],
            'about' => ['/عن-المحامي-ريان-الجهني'],
            'licenses' => ['/التراخيص-والاعتمادات'],
            'methodology' => ['/منهجية-العمل'],
            'contact' => ['/تواصل-معنا'],
            'privacy' => ['/سياسة-الخصوصية'],
            'terms' => ['/الشروط-والأحكام'],
            'blog index' => ['/المدونة'],
            'corporate service' => ['/خدمات/محاماة-الشركات'],
            'arbitration service' => ['/خدمات/التحكيم-التجاري'],
            'notarization service' => ['/خدمات/التوثيق'],
            'real estate service' => ['/خدمات/التسجيل-العيني-للعقار'],
        ];
    }

    #[DataProvider('publicPaths')]
    public function test_public_pages_render(string $path): void
    {
        $this->get($path)->assertOk();
    }

    #[DataProvider('publicPaths')]
    public function test_pages_are_arabic_and_rtl(string $path): void
    {
        $this->get($path)
            ->assertSee('lang="ar-SA"', false)
            ->assertSee('dir="rtl"', false);
    }

    /**
     * Exactly one H1 per page. More than one flattens the document outline;
     * none leaves the page without a stated subject.
     */
    #[DataProvider('publicPaths')]
    public function test_each_page_has_exactly_one_h1(string $path): void
    {
        $html = $this->get($path)->getContent();

        $this->assertSame(
            1,
            preg_match_all('/<h1[\s>]/i', $html),
            "Expected exactly one <h1> on {$path}"
        );
    }

    /**
     * The admin-only marker must never reach a visitor. Content::public()
     * strips it; this asserts the whole rendering path actually does.
     */
    #[DataProvider('publicPaths')]
    public function test_no_client_confirmation_marker_is_ever_public(string $path): void
    {
        $this->get($path)->assertDontSee('NEEDS_CLIENT_CONFIRMATION');
    }

    public function test_draft_services_are_not_publicly_reachable(): void
    {
        $draft = Service::query()->where('status', Service::STATUS_DRAFT)->firstOrFail();

        $this->get('/خدمات/'.$draft->slug)->assertNotFound();
    }

    public function test_unknown_paths_return_404(): void
    {
        $this->get('/لا-توجد-هذه-الصفحة')->assertNotFound();
    }

    public function test_admin_area_is_hidden_from_guests(): void
    {
        // 404 rather than 403 — a 403 would confirm the admin exists there.
        $this->get('/admin')->assertRedirect('/login');
    }
}
