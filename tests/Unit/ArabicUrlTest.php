<?php

namespace Tests\Unit;

use App\Support\ArabicSlug;
use App\Support\Content;
use App\Support\Url;
use PHPUnit\Framework\TestCase;

/**
 * The Arabic URL and content helpers.
 *
 * These are pure functions, so they are tested without booting the app —
 * except canonical(), which needs config and is covered in the feature tests.
 */
class ArabicUrlTest extends TestCase
{
    public function test_arabic_path_segments_are_encoded_individually(): void
    {
        $encoded = Url::encodePath('/خدمات/التحكيم-التجاري');

        // Separators survive; the Arabic is percent-encoded.
        $this->assertStringStartsWith('/%D8%AE', $encoded);
        $this->assertSame(2, substr_count($encoded, '/'));
    }

    /**
     * Calling this on an already-encoded path must not double-encode it —
     * otherwise a canonical tag built from a stored URL would drift.
     */
    public function test_encoding_is_idempotent(): void
    {
        $once = Url::encodePath('/خدمات/التوثيق');
        $twice = Url::encodePath($once);

        $this->assertSame($once, $twice);
    }

    public function test_query_strings_are_preserved_unencoded(): void
    {
        $encoded = Url::encodePath('/الخدمات?utm_source=google');

        $this->assertStringEndsWith('?utm_source=google', $encoded);
    }

    public function test_trailing_slashes_are_removed_but_root_survives(): void
    {
        $this->assertSame('/الخدمات', rawurldecode(Url::encodePath('/الخدمات/')));
        $this->assertSame('/', Url::encodePath('/'));
    }

    public function test_paths_normalise_for_comparison(): void
    {
        $expected = '/خدمات/التحكيم';

        $this->assertSame($expected, Url::normalizePath('/خدمات/التحكيم/'));
        $this->assertSame($expected, Url::normalizePath('خدمات/التحكيم'));
        $this->assertSame($expected, Url::normalizePath('/%D8%AE%D8%AF%D9%85%D8%A7%D8%AA/%D8%A7%D9%84%D8%AA%D8%AD%D9%83%D9%8A%D9%85'));
        $this->assertSame($expected, Url::normalizePath('/خدمات/التحكيم?ref=x'));
    }

    // --- Slugs -------------------------------------------------------------

    public function test_arabic_letters_survive_slugging(): void
    {
        $this->assertSame('محاماة-الشركات', ArabicSlug::make('محاماة الشركات'));
    }

    /**
     * Diacritics and tatweel are invisible to a reader but make two
     * identical-looking slugs different strings — and untypeable URLs.
     */
    public function test_diacritics_and_tatweel_are_stripped(): void
    {
        $this->assertSame('التحكيم', ArabicSlug::make('التَّحْكِيم'));
        $this->assertSame('التحكيم', ArabicSlug::make('التـــحكيم'));

        // Tanween counts as a diacritic: "قابلاً" and "قابلا" must not become
        // two different URLs for the same word.
        $this->assertSame('قابلا', ArabicSlug::make('قابلاً'));
    }

    public function test_arabic_indic_digits_become_ascii(): void
    {
        $this->assertSame('المادة-15', ArabicSlug::make('المادة ١٥'));
    }

    public function test_punctuation_is_removed_and_separators_collapse(): void
    {
        $this->assertSame('العقود-والالتزامات', ArabicSlug::make('العقود، والالتزامات!'));
        $this->assertSame('a-b', ArabicSlug::make('a   ---   b'));
    }

    // --- The public-content guard ------------------------------------------

    /**
     * The marker is an instruction to the client inside the CMS. If it ever
     * rendered publicly it would read as broken copy on a law office's site.
     */
    public function test_paragraphs_containing_the_marker_are_removed_entirely(): void
    {
        $html = '<p>نص حقيقي.</p><p>[[NEEDS_CLIENT_CONFIRMATION]] السيرة بانتظار الاعتماد.</p>';

        $output = (string) Content::public($html);

        $this->assertStringContainsString('نص حقيقي', $output);
        $this->assertStringNotContainsString('NEEDS_CLIENT_CONFIRMATION', $output);
        // The whole paragraph goes, not just the token — no empty <p> left.
        $this->assertStringNotContainsString('بانتظار الاعتماد', $output);
    }

    public function test_a_bare_marker_outside_a_block_is_still_stripped(): void
    {
        $this->assertStringNotContainsString(
            'NEEDS_CLIENT_CONFIRMATION',
            (string) Content::public('[[NEEDS_CLIENT_CONFIRMATION]]')
        );
    }

    public function test_content_without_the_marker_passes_through_unchanged(): void
    {
        $html = '<p>فقرة كاملة.</p><ul><li>عنصر</li></ul>';

        $this->assertSame($html, (string) Content::public($html));
    }

    public function test_needs_confirmation_detects_the_marker_for_the_admin(): void
    {
        $this->assertTrue(Content::needsConfirmation('نص [[NEEDS_CLIENT_CONFIRMATION]] هنا'));
        $this->assertFalse(Content::needsConfirmation('نص مكتمل'));
    }
}
