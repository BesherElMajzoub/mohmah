<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'service_category_id', 'slug', 'title', 'h1', 'summary', 'overview',
    'audience', 'scope', 'process', 'faqs', 'license_keys', 'disclaimer',
    'og_image_id', 'seo_title', 'seo_description', 'canonical_url',
    'focus_phrase', 'is_indexable', 'status', 'published_at', 'needs_review',
    'position',
])]
class Service extends Model
{
    use HasSeo, Publishable;

    protected function casts(): array
    {
        return [
            'audience' => 'array',
            'scope' => 'array',
            'process' => 'array',
            'faqs' => 'array',
            'license_keys' => 'array',
            'is_indexable' => 'boolean',
            'needs_review' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function relatedServices(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'related_services', 'service_id', 'related_service_id')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function path(): string
    {
        return '/خدمات/'.$this->slug;
    }

    public function url(): string
    {
        return $this->canonicalUrl();
    }

    /**
     * The summary is the one-paragraph statement of what the service is — the
     * same job a meta description does.
     */
    protected function seoDescriptionFallback(): ?string
    {
        return $this->summary;
    }

    /**
     * The verified licences that are factually relevant to this service.
     *
     * Resolved from config so the numbers have exactly one source of truth and
     * a page can never display a licence the office does not hold.
     *
     * @return array<int, array{key: string, label: string, number: string}>
     */
    public function licenses(): array
    {
        $keys = $this->license_keys ?? [];

        if ($keys === []) {
            return [];
        }

        return array_values(array_filter(
            config('site.licenses'),
            static fn (array $license): bool => in_array($license['key'], $keys, true),
        ));
    }
}
