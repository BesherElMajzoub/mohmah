<?php

namespace App\Support\Seo;

use App\Models\Media;
use App\Support\Url;

/**
 * Everything the <head> needs for one page, resolved in one place.
 *
 * Controllers build this; <x-seo.meta> renders it. Nothing else writes meta
 * tags, which is what keeps "unique title and description on every indexable
 * page" a property of the system rather than a thing to remember per view.
 */
class SeoData
{
    /**
     * @param  array<int, array{title: string, path: string|null}>  $breadcrumbs
     */
    public function __construct(
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly ?string $canonical = null,
        public readonly ?Media $image = null,
        public readonly bool $indexable = true,
        public readonly array $breadcrumbs = [],
        public readonly string $type = 'website',
        public readonly ?string $publishedTime = null,
        public readonly ?string $modifiedTime = null,
        public readonly int $page = 1,
    ) {}

    /**
     * The full <title>, suffixed with the office name.
     *
     * The suffix is skipped when the title already contains it, so the
     * homepage does not read "مكتب المحامي ريان الجهني | مكتب المحامي ريان الجهني".
     */
    public function documentTitle(): string
    {
        $office = config('site.name');

        if (str_contains($this->title, $office)) {
            return $this->title;
        }

        return "{$this->title} | {$office}";
    }

    /**
     * Self-referencing, and page-aware.
     *
     * A paginated listing keeps `?page=N` in its canonical. Pointing page 2 at
     * page 1 while page 2 is also noindex sends Google two contradictory
     * instructions about the same URL — "this is a duplicate of that one" and
     * "drop it" — and the listing's own pagination is what suffers.
     */
    public function canonicalUrl(): string
    {
        $base = $this->canonical ?? Url::canonical(request()->path());

        return $this->page > 1 ? $base.'?page='.$this->page : $base;
    }

    /**
     * Three gates, in order of authority.
     *
     * The environment gate is absolute: a page marked indexable on a staging
     * deploy still emits noindex, because config('site.indexable') is false
     * there — and nofollow with it, since nothing on that host should be
     * crawled at all.
     *
     * The editorial gate (an admin unticking "indexable", the legal
     * boilerplate) is a dead end by intent: noindex, nofollow.
     *
     * Pagination is neither. Page 2 of a listing has no search intent of its
     * own, so it stays out of the index — but it is the ONLY crawl path to the
     * articles it links to. `follow` is what keeps those articles reachable
     * through internal links rather than the sitemap alone.
     */
    public function robots(): string
    {
        if (! config('site.indexable') || ! $this->indexable) {
            return 'noindex, nofollow';
        }

        if ($this->page > 1) {
            return 'noindex, follow';
        }

        return 'index, follow, max-image-preview:large, max-snippet:-1';
    }

    public function imageUrl(): string
    {
        if ($this->image) {
            return $this->image->absoluteUrl();
        }

        return Url::host().'/'.ltrim((string) config('site.og_image'), '/');
    }

    /**
     * Returns a copy with breadcrumbs attached.
     *
     * Controllers usually know the title and description up front but build
     * the trail separately, so this avoids restating every argument.
     *
     * @param  array<int, array{title: string, path: string|null}>  $breadcrumbs
     */
    public function withBreadcrumbs(array $breadcrumbs): self
    {
        return new self(
            title: $this->title,
            description: $this->description,
            canonical: $this->canonical,
            image: $this->image,
            indexable: $this->indexable,
            breadcrumbs: $breadcrumbs,
            type: $this->type,
            publishedTime: $this->publishedTime,
            modifiedTime: $this->modifiedTime,
            page: $this->page,
        );
    }
}
