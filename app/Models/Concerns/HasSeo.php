<?php

namespace App\Models\Concerns;

use App\Models\Media;
use App\Support\Url;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shared SEO behaviour for services, posts, pages and categories.
 *
 * Each model supplies its own `path()`; everything else — the canonical URL,
 * the title fallback chain, the robots directive — is decided here so the
 * rules cannot drift apart between content types.
 */
trait HasSeo
{
    /**
     * The application path for this record, e.g. '/خدمات/التحكيم-التجاري'.
     */
    abstract public function path(): string;

    /**
     * The href to use for internal links.
     *
     * Percent-encoded, matching the canonical spelling exactly. Browsers
     * would encode a raw Arabic href anyway, but emitting the encoded form
     * ourselves means the HTML a crawler reads and the canonical tag it
     * compares against are byte-identical.
     */
    public function href(): string
    {
        return Url::encodePath($this->path());
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_id');
    }

    /**
     * The canonical URL for this record.
     *
     * An explicit canonical_url in the admin wins — that is the escape hatch
     * for a page that deliberately points at another. Otherwise it is derived,
     * which is what makes canonical tags correct by default.
     */
    public function canonicalUrl(): string
    {
        if (filled($this->canonical_url)) {
            return $this->canonical_url;
        }

        return Url::canonical($this->path());
    }

    public function metaTitle(): string
    {
        return filled($this->seo_title) ? $this->seo_title : $this->title;
    }

    public function metaDescription(): ?string
    {
        return filled($this->seo_description) ? $this->seo_description : null;
    }

    /**
     * Whether this record may be indexed.
     *
     * Two gates, both of which must pass. The per-record flag is editorial;
     * the site-wide flag keeps local and staging environments out of the
     * index no matter what the content says.
     */
    public function isIndexable(): bool
    {
        return (bool) ($this->is_indexable ?? true)
            && (bool) config('site.indexable');
    }

    public function robotsDirective(): string
    {
        return $this->isIndexable()
            ? 'index, follow, max-image-preview:large'
            : 'noindex, nofollow';
    }
}
