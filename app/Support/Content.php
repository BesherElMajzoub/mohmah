<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * The guard that keeps admin-only markers off the public site.
 *
 * Where a page structurally needs a real-world fact the client has not
 * supplied — a biography, an address, office hours — the seeded copy carries
 * [[NEEDS_CLIENT_CONFIRMATION]]. That marker is a message to the client
 * inside the CMS, and it must never reach a visitor.
 *
 * Public templates render body copy through here rather than echoing it
 * directly, so the marker is stripped by the rendering path itself rather
 * than by remembering to check. A feature test asserts no public response
 * ever contains it.
 */
class Content
{
    public const MARKER = '[[NEEDS_CLIENT_CONFIRMATION]]';

    /**
     * Render CMS HTML for public display, with markers removed.
     *
     * The marker usually sits inside a paragraph of its own; removing just
     * the token would leave an empty <p>, so a paragraph that contains
     * nothing else is dropped entirely.
     */
    public static function public(?string $html): HtmlString
    {
        if ($html === null || trim($html) === '') {
            return new HtmlString('');
        }

        // Drop any block-level element whose only meaningful content is the
        // marker (with or without surrounding explanatory text).
        $html = preg_replace(
            '#<(p|li|div)\b[^>]*>(?:(?!</\1>).)*'.preg_quote(self::MARKER, '#').'(?:(?!</\1>).)*</\1>#isu',
            '',
            $html,
        ) ?? $html;

        // Belt and braces: strip a bare marker outside any block element.
        $html = str_replace(self::MARKER, '', $html);

        return new HtmlString(trim($html));
    }

    /**
     * Plain-text variant, for meta descriptions and excerpts.
     */
    public static function publicText(?string $text): string
    {
        return trim(str_replace(self::MARKER, '', (string) $text));
    }

    /**
     * Whether a value still needs a client-supplied fact.
     *
     * Used by the admin to badge the record; never used publicly.
     */
    public static function needsConfirmation(?string $value): bool
    {
        return str_contains((string) $value, self::MARKER);
    }
}
