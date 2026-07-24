<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Seo\SeoData;
use Illuminate\View\View;

/**
 * The homepage is the office's principal local authority page.
 *
 * There is deliberately no separate /محامي-جدة page: a second page targeting
 * the same intent would compete with this one and add nothing a reader could
 * not find here. Jeddah relevance is established through genuine content —
 * the office's location, the courts and registries it works with — not by
 * repeating the phrase.
 */
class HomeController extends Controller
{
    public function __invoke(): View
    {
        $seo = new SeoData(
            title: 'مكتب المحامي ريان الجهني | محامي شركات وتوثيق وقضايا عقارية في جدة',
            description: 'مكتب محاماة في جدة: محامي شركات وعقود وتأسيس وتصفية، قضايا ومنازعات عقارية، توثيق معتمد، ومنازعات شركات التأمين. محامٍ سعودي مرخّص لدى وزارة العدل.',
        );

        return view('pages.home', [
            'seo' => $seo,
            'pillars' => ServiceCategory::query()
                ->with(['services' => fn ($q) => $q->published()->orderBy('position')->limit(4)])
                ->orderBy('position')
                ->get(),
            // A curated selection rather than everything — the homepage points
            // into the service tree, it does not reproduce it.
            'featuredServices' => Service::query()
                ->published()
                ->with('category')
                ->orderBy('position')
                ->limit(6)
                ->get(),
            'latestPosts' => Post::query()
                ->published()
                ->with('category')
                ->latest('published_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
