<?php

namespace App\Support\Seo;

use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Support\Settings;
use App\Support\Url;

/**
 * JSON-LD builders.
 *
 * One rule governs this whole class: every property emitted must correspond
 * to something factually true that a visitor can see on the same page.
 *
 * That rule is why there is no aggregateRating, no review, no award, no
 * foundingDate, no numberOfEmployees and no FAQPage builder here. Those are
 * the properties that earn rich results, and every one of them would require
 * inventing a fact the client has not supplied. Marking up claims the page
 * does not make is a manual-action risk, and for a law office it is a
 * credential misrepresentation as well.
 *
 * Address, telephone, opening hours and social profiles appear only once the
 * client has entered real values — see the filled() guards.
 */
class Schema
{
    public function __construct(private readonly Settings $settings) {}

    /**
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        $schema = [
            '@type' => ['LegalService', 'Attorney'],
            '@id' => Url::host().'/#organization',
            'name' => config('site.name'),
            'url' => Url::host().'/',
            'telephone' => config('site.phone_e164'),
            'image' => Url::host().'/'.ltrim((string) config('site.logo'), '/'),
            'logo' => Url::host().'/'.ltrim((string) config('site.logo'), '/'),
            'areaServed' => [
                '@type' => 'City',
                'name' => config('site.city'),
            ],
            'knowsLanguage' => ['ar'],
        ];

        // --- Everything below is conditional on real supplied data ---------

        if ($this->settings->filled('office_email')) {
            $schema['email'] = $this->settings->get('office_email');
        }

        $address = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $this->settings->get('office_address'),
            'addressLocality' => config('site.city'),
            'addressRegion' => config('site.region'),
            'addressCountry' => config('site.country_code'),
        ]);

        // Only claim a physical address once the street address is real; a
        // city-only PostalAddress on a LocalBusiness invites a mismatch with
        // the Business Profile the office may later verify.
        if ($this->settings->filled('office_address')) {
            $schema['address'] = $address;
        }

        if ($this->settings->filled('office_hours_schema')) {
            $schema['openingHours'] = $this->settings->get('office_hours_schema');
        }

        if ($this->settings->filled('map_url')) {
            $schema['hasMap'] = $this->settings->get('map_url');
        }

        $social = array_values(array_filter((array) $this->settings->get('social_links', [])));

        if ($social !== []) {
            $schema['sameAs'] = $social;
        }

        return $this->document($schema);
    }

    /**
     * The lawyer, for the profile page.
     *
     * Deliberately minimal. alumniOf, award, knowsAbout-as-expertise and
     * yearsOfExperience are all absent because none were supplied.
     *
     * @return array<string, mixed>
     */
    public function person(Page $page): array
    {
        return $this->document([
            '@type' => 'Person',
            '@id' => Url::host().'/#lawyer',
            'name' => config('site.lawyer_name'),
            'jobTitle' => 'محامٍ',
            'url' => Url::canonical($page->path()),
            'telephone' => config('site.phone_e164'),
            'worksFor' => ['@id' => Url::host().'/#organization'],
            'workLocation' => [
                '@type' => 'Place',
                'name' => config('site.city'),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function service(Service $service): array
    {
        $schema = [
            '@type' => 'Service',
            '@id' => $service->canonicalUrl().'#service',
            'name' => $service->h1,
            'serviceType' => $service->category->title,
            'url' => $service->canonicalUrl(),
            'provider' => ['@id' => Url::host().'/#organization'],
            'areaServed' => [
                '@type' => 'City',
                'name' => config('site.city'),
            ],
        ];

        if (filled($service->summary)) {
            $schema['description'] = $service->summary;
        }

        // hasOfferCatalog mirrors the visible "نطاق العمل" list — it is only
        // emitted when that list actually appears on the page.
        $scope = array_values(array_filter((array) $service->scope));

        if ($scope !== []) {
            $schema['hasOfferCatalog'] = [
                '@type' => 'OfferCatalog',
                'name' => 'نطاق العمل',
                'itemListElement' => array_map(
                    static fn (string $item): array => [
                        '@type' => 'Offer',
                        'itemOffered' => ['@type' => 'Service', 'name' => $item],
                    ],
                    $scope,
                ),
            ];
        }

        return $this->document($schema);
    }

    /**
     * @return array<string, mixed>
     */
    public function article(Post $post): array
    {
        $schema = [
            '@type' => 'Article',
            '@id' => $post->canonicalUrl().'#article',
            'headline' => $post->heading(),
            'url' => $post->canonicalUrl(),
            'mainEntityOfPage' => $post->canonicalUrl(),
            'inLanguage' => 'ar',
            'publisher' => ['@id' => Url::host().'/#organization'],
            'articleSection' => $post->category->title,
        ];

        if (filled($post->excerpt)) {
            $schema['description'] = $post->excerpt;
        }

        if ($post->published_at) {
            $schema['datePublished'] = $post->published_at->toIso8601String();
        }

        // Prefer the editorial "content updated" date over the row's
        // updated_at, which changes for edits a reader would never notice.
        $modified = $post->content_updated_at ?? $post->updated_at;

        if ($modified) {
            $schema['dateModified'] = $modified->toIso8601String();
        }

        if (filled($post->author_name)) {
            $schema['author'] = ['@type' => 'Person', 'name' => $post->author_name];
        }

        // Only claim review when a named reviewer is actually credited on the
        // page — this is the property that signals legal oversight, so it has
        // to be true.
        if (filled($post->reviewer_name)) {
            $schema['reviewedBy'] = ['@type' => 'Person', 'name' => $post->reviewer_name];
        }

        if ($post->coverImage) {
            $schema['image'] = $post->coverImage->absoluteUrl();
        }

        return $this->document($schema);
    }

    /**
     * Mirrors the visible breadcrumb trail. Emitted only when breadcrumbs are
     * actually rendered on the page.
     *
     * @param  array<int, array{title: string, path: string|null}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbs(array $items): array
    {
        $elements = [];

        foreach (array_values($items) as $i => $item) {
            $element = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['title'],
            ];

            // The final crumb is the current page and carries no item URL,
            // per Google's guidance.
            if (! empty($item['path'])) {
                $element['item'] = Url::canonical($item['path']);
            }

            $elements[] = $element;
        }

        return $this->document([
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ]);
    }

    public function website(): array
    {
        return $this->document([
            '@type' => 'WebSite',
            '@id' => Url::host().'/#website',
            'name' => config('site.name'),
            'url' => Url::host().'/',
            'inLanguage' => 'ar',
            'publisher' => ['@id' => Url::host().'/#organization'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private function document(array $schema): array
    {
        return ['@context' => 'https://schema.org'] + $schema;
    }

    /**
     * Encode for a <script type="application/ld+json"> block.
     *
     * Unicode is left unescaped so the Arabic stays legible when validating,
     * and slashes are unescaped so URLs read normally.
     *
     * @param  array<string, mixed>  $schema
     */
    public static function encode(array $schema): string
    {
        return (string) json_encode(
            $schema,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
    }
}
