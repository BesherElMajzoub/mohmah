<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Service;
use App\Support\Url;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * The XML sitemap, generated from the database rather than a build artefact.
 *
 * The requirement driving this design: publishing an article must put it in
 * the sitemap, with no second step. Because the list is a live query, a new
 * article is included the moment it is published — and because
 * SitemapCacheObserver clears CACHE_KEY on every save of a Post, Service,
 * Page or PostCategory, the cache can never serve a stale list.
 *
 * The inclusion rule is the `forSitemap` scope shared with the public
 * controllers, so the sitemap physically cannot advertise a URL that would
 * 404: a draft, a scheduled article, or a noindex page is excluded from both
 * by the same clause. Admin, login, previews and the tracking endpoint are
 * never candidates because they are not content models at all.
 */
class SitemapController extends Controller
{
    public const CACHE_KEY = 'sitemap.xml';

    /**
     * Long TTL is safe because writes invalidate it explicitly. This value
     * only bounds how long a change made *outside* the app (a direct SQL
     * edit) could take to appear.
     */
    private const CACHE_TTL_MINUTES = 60;

    public function __invoke(): Response
    {
        $xml = Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn (): string => $this->render($this->entries()),
        );

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    /**
     * @return array<int, array{loc: string, lastmod: ?string, changefreq: string, priority: string}>
     */
    private function entries(): array
    {
        $entries = [];

        // --- Static routes -------------------------------------------------
        $entries[] = $this->entry('/', null, 'weekly', '1.0');
        $entries[] = $this->entry('/الخدمات', null, 'monthly', '0.9');
        $entries[] = $this->entry('/تواصل-معنا', null, 'yearly', '0.7');

        // The blog index is only worth listing once it has something on it.
        if (Post::query()->forSitemap()->exists()) {
            $entries[] = $this->entry('/المدونة', null, 'weekly', '0.7');
        }

        // --- Editable pages ------------------------------------------------
        Page::query()
            ->where('is_indexable', true)
            ->get()
            ->each(function (Page $page) use (&$entries) {
                $entries[] = $this->entry(
                    $page->path(),
                    $page->updated_at?->toAtomString(),
                    'yearly',
                    // The licences page is a primary trust signal; the legal
                    // boilerplate is not.
                    in_array($page->key, [Page::KEY_ABOUT, Page::KEY_LICENSES], true) ? '0.8' : '0.3',
                );
            });

        // --- Services ------------------------------------------------------
        Service::query()
            ->forSitemap()
            ->get()
            ->each(function (Service $service) use (&$entries) {
                $entries[] = $this->entry(
                    $service->path(),
                    $service->updated_at?->toAtomString(),
                    'monthly',
                    '0.8',
                );
            });

        // --- Blog categories -----------------------------------------------
        PostCategory::query()
            ->get()
            ->filter(fn (PostCategory $c) => $c->publishedPosts()->exists())
            ->each(function (PostCategory $category) use (&$entries) {
                $entries[] = $this->entry($category->path(), null, 'weekly', '0.6');
            });

        // --- Articles ------------------------------------------------------
        Post::query()
            ->forSitemap()
            ->get()
            ->each(function (Post $post) use (&$entries) {
                $entries[] = $this->entry(
                    $post->path(),
                    ($post->content_updated_at ?? $post->updated_at)?->toAtomString(),
                    'yearly',
                    '0.6',
                );
            });

        return $entries;
    }

    /**
     * @return array{loc: string, lastmod: ?string, changefreq: string, priority: string}
     */
    private function entry(string $path, ?string $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => Url::canonical($path),
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }

    /**
     * @param  array<int, array{loc: string, lastmod: ?string, changefreq: string, priority: string}>  $entries
     */
    private function render(array $entries): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($entries as $entry) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.htmlspecialchars($entry['loc'], ENT_XML1).'</loc>';

            if ($entry['lastmod']) {
                $lines[] = '    <lastmod>'.$entry['lastmod'].'</lastmod>';
            }

            $lines[] = '    <changefreq>'.$entry['changefreq'].'</changefreq>';
            $lines[] = '    <priority>'.$entry['priority'].'</priority>';
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines);
    }
}
