<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'post_category_id', 'slug', 'title', 'h1', 'excerpt', 'body',
    'cover_image_id', 'og_image_id', 'author_name', 'reviewer_name',
    'reading_minutes', 'seo_title', 'seo_description', 'canonical_url',
    'focus_phrase', 'is_indexable', 'status', 'published_at',
    'content_updated_at', 'needs_review',
])]
class Post extends Model
{
    use HasSeo, Publishable;

    /**
     * Average Arabic reading speed, words per minute. Used only to show the
     * reader an honest estimate, so a round figure is appropriate.
     */
    private const WORDS_PER_MINUTE = 200;

    protected function casts(): array
    {
        return [
            'is_indexable' => 'boolean',
            'needs_review' => 'boolean',
            'published_at' => 'datetime',
            'content_updated_at' => 'datetime',
            'reading_minutes' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_image_id');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function relatedPosts(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'related_posts', 'post_id', 'related_post_id')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function path(): string
    {
        return "/المدونة/{$this->slug}";
    }

    public function heading(): string
    {
        return filled($this->h1) ? $this->h1 : $this->title;
    }

    /**
     * Recalculate the reading estimate from the article body.
     *
     * Tags are stripped first so markup does not inflate the count.
     */
    public function calculateReadingMinutes(): int
    {
        $text = trim(strip_tags((string) $this->body));

        if ($text === '') {
            return 1;
        }

        $words = count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        return max(1, (int) ceil($words / self::WORDS_PER_MINUTE));
    }
}
