<?php

namespace App\Support;

/**
 * Builds the site's canonical URLs.
 *
 * Every slug on this site is raw UTF-8 Arabic, which means a single page can
 * be spelled two ways: decoded (‎/الخدمات/التحكيم-التجاري‎) and percent-encoded
 * (/%D8%A7%D9%84%D8%AE...). Browsers, crawlers and copy-pasted links mix the
 * two freely. If the canonical tag, the sitemap entry and the JSON-LD @id do
 * not agree byte-for-byte, Google sees three URLs where there is one page.
 *
 * So every public URL the application emits goes through here, and here alone
 * decides the spelling: percent-encoded, on the canonical host, no trailing
 * slash.
 */
class Url
{
    /**
     * Absolute canonical URL for an application path.
     */
    public static function canonical(string $path = '/'): string
    {
        return static::host().static::encodePath($path);
    }

    /**
     * The canonical scheme + host, without a trailing slash.
     */
    public static function host(): string
    {
        return rtrim((string) config('site.canonical_url'), '/');
    }

    /**
     * Percent-encode a path while leaving the separators intact.
     *
     * rawurlencode() on the whole path would escape the slashes too, so each
     * segment is encoded on its own. Segments that are already encoded are
     * decoded first, which makes this safe to call on a value that may or may
     * not have been through here before.
     */
    public static function encodePath(string $path): string
    {
        $path = '/'.ltrim($path, '/');

        // Preserve a querystring/fragment untouched — only the path is encoded.
        $suffix = '';
        if (($cut = strcspn($path, '?#')) < strlen($path)) {
            $suffix = substr($path, $cut);
            $path = substr($path, 0, $cut);
        }

        $encoded = implode('/', array_map(
            static fn (string $segment): string => rawurlencode(rawurldecode($segment)),
            explode('/', $path)
        ));

        // Collapse the root back to a bare slash rather than an empty string.
        $encoded = $encoded === '' ? '/' : $encoded;

        // No trailing slash anywhere except the root itself.
        if ($encoded !== '/') {
            $encoded = rtrim($encoded, '/');
        }

        return $encoded.$suffix;
    }

    /**
     * Normalise an incoming request path for comparison and storage.
     *
     * Decoded, leading slash, no trailing slash, no query. Used by the
     * redirect table so a legacy Arabic URL matches whether the crawler
     * requested it encoded or not.
     */
    public static function normalizePath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?: '/';
        $path = rawurldecode($path);
        $path = '/'.trim($path, '/');

        return $path;
    }
}
