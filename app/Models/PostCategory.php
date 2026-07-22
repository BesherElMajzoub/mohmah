<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'title', 'intro', 'seo_title', 'seo_description', 'position'])]
class PostCategory extends Model
{
    use HasSeo;

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->latest('published_at');
    }

    public function publishedPosts(): HasMany
    {
        return $this->posts()->published();
    }

    public function path(): string
    {
        return "/المدونة/{$this->slug}";
    }

    /**
     * A category listing is worth indexing only once it actually has articles
     * on it — an empty listing is a thin page.
     */
    public function isIndexable(): bool
    {
        return (bool) config('site.indexable')
            && $this->publishedPosts()->exists();
    }
}
