<?php

namespace App\Providers;

use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Service;
use App\Models\Setting;
use App\Observers\SettingsCacheObserver;
use App\Observers\SitemapCacheObserver;
use App\Support\Settings;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One instance per request so the settings table is read once, not
        // once per header/footer/page lookup.
        $this->app->singleton(Settings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerObservers();
        $this->forceHttpsInProduction();
    }

    /**
     * Anything that can appear in the sitemap flushes it when it changes, so
     * publishing an article is the entire publishing workflow.
     */
    private function registerObservers(): void
    {
        foreach ([Post::class, Service::class, Page::class, PostCategory::class] as $model) {
            $model::observe(SitemapCacheObserver::class);
        }

        Setting::observe(SettingsCacheObserver::class);
    }

    /**
     * Behind a proxy that terminates TLS, Laravel would otherwise generate
     * http:// asset and form URLs on an https:// page, causing mixed-content
     * blocks. Local development is left alone.
     */
    private function forceHttpsInProduction(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
