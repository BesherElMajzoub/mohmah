<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Canonical host
    |--------------------------------------------------------------------------
    |
    | Every canonical tag, sitemap entry, Open Graph URL and JSON-LD @id is
    | built from this value so the site emits exactly one spelling of every
    | URL. Overridable per-environment so staging does not advertise itself
    | as the production host.
    |
    */

    'canonical_url' => rtrim((string) env('SITE_CANONICAL_URL', 'https://mohamah-ksa.com'), '/'),

    /*
    |--------------------------------------------------------------------------
    | Identity
    |--------------------------------------------------------------------------
    |
    | Supplied by the client and treated as verified fact. Anything not listed
    | here has NOT been supplied and must never be invented — see the settings
    | table for values the client fills in later (address, hours, email...).
    |
    */

    'name' => 'مكتب المحامي ريان الجهني',
    'lawyer_name' => 'ريان الجهني',
    'city' => 'جدة',
    'region' => 'منطقة مكة المكرمة',
    'country' => 'المملكة العربية السعودية',
    'country_code' => 'SA',

    /*
    |--------------------------------------------------------------------------
    | Conversion actions
    |--------------------------------------------------------------------------
    |
    | 'display' is what a visitor reads; 'tel' and 'whatsapp' are the machine
    | targets. They are deliberately separate values — the local format is the
    | readable one, the E.164 format is the dialable one.
    |
    */

    'phone_display' => '0536100125',
    'phone_e164' => '+966536100125',
    'tel_href' => 'tel:+966536100125',
    'whatsapp_href' => 'https://wa.me/966536100125',

    /*
    |--------------------------------------------------------------------------
    | Verified licences
    |--------------------------------------------------------------------------
    |
    | Rendered EXACTLY as supplied, including the Arabic-Indic digits and the
    | spacing in the arbitration number. Do not normalise, reformat, or add an
    | issuing authority, issue date, expiry, or verification link — none were
    | supplied and inventing one would be a false credential claim.
    |
    */

    'licenses' => [
        [
            'key' => 'advocacy',
            'label' => 'رقم ترخيص المحاماة',
            'number' => '432200',
        ],
        [
            'key' => 'notarization',
            'label' => 'رقم ترخيص التوثيق',
            'number' => '٤٤/١٢٤٣',
        ],
        [
            'key' => 'arbitration',
            'label' => 'رقم ترخيص التحكيم',
            'number' => 'ADR - 0017 4',
        ],
        [
            'key' => 'real_estate',
            'label' => 'رقم ترخيص التسجيل العيني',
            'number' => '2223000528',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding assets
    |--------------------------------------------------------------------------
    */

    'logo' => 'brand/logo.png',
    'og_image' => 'brand/og-default.png',

    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    |
    | Left empty until the client supplies real IDs. When empty, no analytics
    | script is rendered at all — we do not ship a tracker with a placeholder
    | measurement ID.
    |
    */

    'ga4_id' => env('SITE_GA4_ID'),
    'google_ads_id' => env('SITE_GOOGLE_ADS_ID'),
    'google_ads_call_label' => env('SITE_GOOGLE_ADS_CALL_LABEL'),
    'google_ads_whatsapp_label' => env('SITE_GOOGLE_ADS_WHATSAPP_LABEL'),
    'search_console_token' => env('SITE_SEARCH_CONSOLE_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Indexing
    |--------------------------------------------------------------------------
    |
    | When false, robots.txt disallows everything and every page emits
    | noindex. Guards against a staging deploy being indexed.
    |
    */

    'indexable' => (bool) env('SITE_INDEXABLE', false),

];
