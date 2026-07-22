<?php

namespace App\Http\Controllers;

use App\Support\Url;
use Illuminate\Http\Response;

/**
 * robots.txt as a route rather than a static file.
 *
 * Two reasons it cannot be static: the sitemap URL depends on the canonical
 * host config, and a non-production deploy must disallow everything. A file
 * committed to public/ would ship the production rules to staging.
 */
class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $lines = ['User-agent: *'];

        if (config('site.indexable')) {
            $lines[] = 'Allow: /';
            $lines[] = '';
            // Paths with no value in the index. Note these are *also* noindex
            // at the page level — robots.txt only stops the crawl, it does not
            // remove a URL that is already indexed.
            $lines[] = 'Disallow: /admin';
            $lines[] = 'Disallow: /login';
            $lines[] = 'Disallow: /t/';
            $lines[] = 'Disallow: /*?utm_';
            $lines[] = 'Disallow: /*?gclid=';
            $lines[] = '';
            $lines[] = 'Sitemap: '.Url::host().'/sitemap.xml';
        } else {
            // Local and staging: nothing may be crawled.
            $lines[] = 'Disallow: /';
        }

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
