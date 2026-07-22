<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A practice pillar. Groups services for navigation; it has no page of its
 * own, so it carries no SEO fields — see path().
 */
#[Fillable(['slug', 'title', 'menu_label', 'intro', 'position'])]
class ServiceCategory extends Model
{
    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('position');
    }

    public function publishedServices(): HasMany
    {
        return $this->services()->published();
    }

    public function path(): string
    {
        // Categories group services in the navigation but have no landing page
        // of their own — the services index is the single entry point, which
        // avoids a thin duplicate page per pillar.
        return '/الخدمات#'.$this->slug;
    }

    public function menuLabel(): string
    {
        return filled($this->menu_label) ? $this->menu_label : $this->title;
    }
}
