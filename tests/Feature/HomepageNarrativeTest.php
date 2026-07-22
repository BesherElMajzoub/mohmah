<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The homepage argues in a specific order, and the order is the point.
 *
 * It opens on the weight of the decision, then lets the reader self-qualify,
 * then shows the office is licensed, then states what it will and will not
 * promise — and only after all of that does it list services. A later edit
 * that hoists the service grid to the top would quietly undo the whole
 * approach while every other test still passed, so the sequence is asserted
 * here directly.
 */
class HomepageNarrativeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SettingsSeeder::class, TaxonomySeeder::class, ServiceSeeder::class, PageSeeder::class]);
    }

    public function test_the_page_argues_in_the_intended_order(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $sequence = [
            'عندما تكون قيمة القرار كبيرة',   // the decision's weight
            'هل يناسبك هذا المكتب؟',           // self-qualification
            'تراخيص مهنية متعددة',             // licensed and capable
            'لا نبيع وعوداً',                   // the position
            'أربعة مجالات رئيسية',             // and only now, services
            'كل ملف مهم يبدأ بخطوة واضحة',     // the close
        ];

        $previous = -1;

        foreach ($sequence as $marker) {
            $position = mb_strpos($html, $marker);

            $this->assertNotFalse($position, "Missing homepage section: {$marker}");
            $this->assertGreaterThan(
                $previous,
                $position,
                "Homepage section is out of order: {$marker}"
            );

            $previous = $position;
        }
    }

    /**
     * The service grid must not be the first thing a visitor meets.
     */
    public function test_services_do_not_open_the_page(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertLessThan(
            mb_strpos($html, 'أربعة مجالات رئيسية'),
            mb_strpos($html, 'عندما تكون قيمة القرار كبيرة'),
            'The hero must precede the service list.'
        );
    }

    /**
     * The office refuses to promise outcomes. That sentence is the single
     * most load-bearing piece of copy on the site — it is what distinguishes
     * it from the promise-led legal sites it competes with.
     */
    public function test_the_page_refuses_to_promise_an_outcome(): void
    {
        $this->get('/')
            ->assertSee('لا نبيع وعوداً، بل نبني مساراً قانونياً واضحاً')
            ->assertSee('لا يمكن لأي محامٍ أن يضمن نتيجة قضية أو معاملة');
    }

    /**
     * The portrait is the hero's LCP element, so it must be served from the
     * derived WebP set — never the 1.4 MB source PNG, which would be roughly
     * fifteen times the weight of the whole rest of the page.
     */
    public function test_the_hero_portrait_is_served_optimised(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('brand/lawyer-portrait-750.webp', $html);
        $this->assertStringContainsString('srcset', $html);

        $this->assertStringNotContainsString(
            'saudi-lawyer-hero-transparent.png',
            $html,
            'The unoptimised source PNG must never be served to visitors.'
        );
    }

    /**
     * The portrait is of a real, named person. Its alt text names him rather
     * than describing a generic figure, and the intrinsic dimensions are
     * declared so the hero does not shift as it decodes.
     */
    public function test_the_portrait_is_attributed_and_reserves_its_space(): void
    {
        $this->get('/')
            ->assertSee('alt="المحامي ريان الجهني"', false)
            ->assertSee('width="1003"', false)
            ->assertSee('height="1568"', false);
    }

    /**
     * Both primary actions sit directly under the opening statement, before
     * any qualification step — a serious caller must never have to scroll
     * past a form to reach the phone.
     */
    public function test_both_actions_appear_before_the_qualification_section(): void
    {
        $html = $this->get('/')->getContent();

        $heroCall = mb_strpos($html, 'data-placement="hero"');
        $qualifier = mb_strpos($html, 'هل يناسبك هذا المكتب؟');

        $this->assertNotFalse($heroCall);
        $this->assertLessThan($qualifier, $heroCall);
    }
}
