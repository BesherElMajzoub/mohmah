<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Seo\SeoData;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $seo = (new SeoData(
            title: 'الخدمات القانونية',
            description: 'خدمات مكتب المحامي ريان الجهني في محاماة الأعمال والتحكيم التجاري والتوثيق والخدمات العقارية للشركات والمستثمرين وملّاك العقارات.',
        ))->withBreadcrumbs([
            ['title' => 'الرئيسية', 'path' => '/'],
            ['title' => 'الخدمات', 'path' => null],
        ]);

        return view('pages.services.index', [
            'seo' => $seo,
            'categories' => ServiceCategory::query()
                ->with(['services' => fn ($q) => $q->published()->orderBy('position')])
                ->orderBy('position')
                ->get(),
        ]);
    }

    public function show(Service $service): View
    {
        // A draft or scheduled service is a 404 for the public, exactly as the
        // sitemap assumes.
        abort_unless($service->isPublished(), 404);

        $service->load([
            'category',
            'relatedServices' => fn ($q) => $q->published(),
            'posts' => fn ($q) => $q->published(),
        ]);

        $breadcrumbs = [
            ['title' => 'الرئيسية', 'path' => '/'],
            ['title' => 'الخدمات', 'path' => '/الخدمات'],
            ['title' => $service->title, 'path' => null],
        ];

        $seo = (new SeoData(
            title: $service->metaTitle(),
            description: $service->metaDescription(),
            canonical: $service->canonicalUrl(),
            image: $service->ogImage,
            indexable: (bool) $service->is_indexable,
        ))->withBreadcrumbs($breadcrumbs);

        return view('pages.services.show', [
            'seo' => $seo,
            'service' => $service,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
