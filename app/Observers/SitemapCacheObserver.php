<?php

namespace App\Observers;

use App\Http\Controllers\SitemapController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Keeps /sitemap.xml current without a build step or a cron job.
 *
 * The sitemap is generated from a live query and cached, so the only thing
 * standing between "the client publishes an article" and "the article is in
 * the sitemap" is this cache entry. Attaching to saved/deleted on every model
 * that can appear in the sitemap means publishing is the whole workflow — the
 * client never has to remember a second step, and there is no window where
 * the sitemap contradicts the site.
 *
 * Registered in AppServiceProvider for Post, Service, Page and PostCategory.
 */
class SitemapCacheObserver
{
    public function saved(Model $model): void
    {
        $this->flush();
    }

    public function deleted(Model $model): void
    {
        $this->flush();
    }

    private function flush(): void
    {
        Cache::forget(SitemapController::CACHE_KEY);
    }
}
