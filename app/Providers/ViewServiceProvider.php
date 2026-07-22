<?php

namespace App\Providers;

use App\Models\ServiceCategory;
use App\Support\Seo\Schema;
use App\Support\Settings;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Supplies the data every public page needs regardless of controller:
 * the navigation tree, the settings object, and the site-wide JSON-LD.
 *
 * Doing this with composers rather than passing it from each controller
 * means a new page cannot accidentally ship without navigation or schema.
 */
class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->composeLayout();
        $this->composeNavigation();
        $this->shareSettings();
    }

    private function composeLayout(): void
    {
        View::composer('components.layouts.public', function ($view) {
            $schema = app(Schema::class);

            $view->with([
                'organizationSchema' => $schema->organization(),
                'websiteSchema' => $schema->website(),
            ]);
        });
    }

    /**
     * The mega menu and footer both need the full service tree.
     *
     * Queried per request rather than cached. It is two indexed queries with
     * eager loading against a table of a few dozen rows — cheaper than the
     * correctness cost of caching Eloquent models, which have to be
     * serialised into the store and go stale the moment a service is edited.
     *
     * The result is memoised for the request so the header and footer, which
     * both use it, share one query.
     */
    private function composeNavigation(): void
    {
        View::composer(['components.site.header', 'components.site.footer'], function ($view) {
            $view->with('navCategories', once(fn () => ServiceCategory::query()
                ->with(['services' => fn ($q) => $q->published()->orderBy('position')])
                ->orderBy('position')
                ->get()));
        });
    }

    private function shareSettings(): void
    {
        View::share('settings', app(Settings::class));
    }
}
