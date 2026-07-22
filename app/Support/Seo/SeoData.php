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

    public function canonicalUrl(): string
    {
        return $this->canonical ?? Url::canonical(request()->path());
    }

    /**
     * Two gates: the page's own decision, and the environment's.
     *
     * A page marked indexable on a staging deploy still emits noindex,
     * because config('site.indexable') is false there.
     */
    public function robots(): string
    {
        return $this->indexable && config('site.indexable')
            ? 'index, follow, max-image-preview:large, max-snippet:-1'
            : 'noindex, nofollow';
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
        );
    }
}
