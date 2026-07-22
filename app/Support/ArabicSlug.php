<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Slug generation for Arabic titles.
 *
 * Laravel's Str::slug() transliterates to ASCII, which turns an Arabic title
 * into either mojibake or an empty string. This site's URLs are Arabic by
 * design, so slugs keep their Arabic letters and only the *shaping* marks are
 * stripped — the characters a reader cannot see but a URL comparison can.
 */
class ArabicSlug
{
    /**
     * Arabic diacritics (harakat) and the tatweel elongation character.
     *
     * These are invisible or purely decorative, but they make two visually
     * identical slugs different strings — and a URL that cannot be retyped.
     */
    private const DIACRITICS = '/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]/u';

    /**
     * Arabic punctuation: comma, semicolon, question mark, full stop,
     * percent sign, decimal separators, ornate parentheses.
     *
     * These live inside the Arabic Unicode block, so \p{Arabic} matches them
     * and the general punctuation filter below would let them straight
     * through into a slug. They have to be removed by name.
     */
    private const PUNCTUATION = '/[\x{060C}\x{061B}\x{061F}\x{06D4}\x{066A}-\x{066D}\x{FD3E}\x{FD3F}\x{0609}\x{060D}]/u';

    public static function make(string $value): string
    {
        // Normalise the Arabic-Indic digits to ASCII so numbers in URLs are
        // typeable on any keyboard.
        $value = strtr($value, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $value = preg_replace(self::DIACRITICS, '', $value) ?? $value;
        $value = preg_replace(self::PUNCTUATION, '', $value) ?? $value;

        // Drop punctuation, keeping Arabic letters, Latin letters, digits and
        // the characters we use as separators.
        $value = preg_replace('/[^\p{Arabic}\p{L}\p{N}\s\-_]+/u', '', $value) ?? $value;

        // Collapse any run of whitespace, underscores or hyphens to one hyphen.
        $value = preg_replace('/[\s_\-]+/u', '-', $value) ?? $value;

        return trim($value, '-');
    }

    /**
     * Make a slug unique within a table, appending -2, -3, … on collision.
     *
     * @param  class-string<Model>  $model
     */
    public static function unique(string $value, string $model, ?int $ignoreId = null): string
    {
        $base = static::make($value);
        $slug = $base;
        $suffix = 1;

        while ($model::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }
}
