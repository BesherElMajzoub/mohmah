<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Service;
use App\Support\Seo\SeoData;
use Illuminate\View\View;

/**
 * The fixed pages.
 *
 * Each action resolves its Page row by immutable key, so the client can
 * rewrite the copy and the SEO fields without any chance of breaking a route
 * or a URL that is already indexed.
 */
class PageController extends Controller
{
    public function about(): View
    {
        $page = Page::byKey(Page::KEY_ABOUT);

        return view('pages.about', [
            'page' => $page,
            'seo' => $this->seo($page, 'عن المحامي'),
            'breadcrumbs' => $this->breadcrumbs($page),
            // The profile page states areas of focus; linking them to the
            // actual service pages is more useful than listing them as text.
            'focusServices' => Service::query()
                ->published()
                ->with('category')
                ->orderBy('position')
                ->limit(8)
                ->get(),
        ]);
    }

    public function licenses(): View
    {
        $page = Page::byKey(Page::KEY_LICENSES);

        return view('pages.licenses', [
            'page' => $page,
            'seo' => $this->seo($page, 'التراخيص والاعتمادات'),
            'breadcrumbs' => $this->breadcrumbs($page),
        ]);
    }

    public function methodology(): View
    {
        $page = Page::byKey(Page::KEY_METHODOLOGY);

        return view('pages.methodology', [
            'page' => $page,
            'seo' => $this->seo($page, 'منهجية العمل'),
            'breadcrumbs' => $this->breadcrumbs($page),
        ]);
    }

    public function privacy(): View
    {
        return $this->legal(Page::KEY_PRIVACY);
    }

    public function terms(): View
    {
        return $this->legal(Page::KEY_TERMS);
    }

    private function legal(string $key): View
    {
        $page = Page::byKey($key);

        return view('pages.legal', [
            'page' => $page,
            'seo' => $this->seo($page, $page->title),
            'breadcrumbs' => $this->breadcrumbs($page),
        ]);
    }

    private function seo(Page $page, string $fallbackTitle): SeoData
    {
        return (new SeoData(
            title: $page->metaTitle() ?: $fallbackTitle,
            description: $page->metaDescription(),
            canonical: $page->canonicalUrl(),
            image: $page->ogImage,
            indexable: (bool) $page->is_indexable,
        ))->withBreadcrumbs($this->breadcrumbs($page));
    }

    /**
     * @return array<int, array{title: string, path: string|null}>
     */
    private function breadcrumbs(Page $page): array
    {
        return [
            ['title' => 'الرئيسية', 'path' => '/'],
            ['title' => $page->title, 'path' => null],
        ];
    }
}
