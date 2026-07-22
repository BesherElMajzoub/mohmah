<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A fixed page whose copy and SEO are editable but whose existence is not.
 *
 * Routes bind by `key`, never by id, so the client can rewrite سياسة الخصوصية
 * freely without any risk of breaking the route that serves it.
 */
#[Fillable([
    'slug', 'title', 'h1', 'intro', 'body', 'sections', 'og_image_id',
    'seo_title', 'seo_description', 'canonical_url', 'is_indexable', 'needs_review',
])]
class Page extends Model
{
    use HasSeo;

    public const KEY_ABOUT = 'about';

    public const KEY_LICENSES = 'licenses';

    public const KEY_METHODOLOGY = 'methodology';

    public const KEY_PRIVACY = 'privacy';

    public const KEY_TERMS = 'terms';

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'is_indexable' => 'boolean',
            'needs_review' => 'boolean',
        ];
    }

    public function path(): string
    {
        return '/'.$this->slug;
    }

    public static function byKey(string $key): self
    {
        return static::query()->where('key', $key)->firstOrFail();
    }
}
