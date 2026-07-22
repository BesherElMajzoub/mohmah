@php
    $ga4 = config('site.ga4_id');
    $ads = config('site.google_ads_id');
@endphp

{{-- Nothing is loaded unless a real measurement ID is configured.

     A placeholder ID would cost every visitor a third-party connection and a
     script parse for data nobody can read. Until the client supplies theirs,
     the site ships no tracker at all — which is also why the pages are fast
     out of the box. --}}

@if ($ga4 || $ads)
    {{-- Consent defaults are declared *before* gtag.js loads, so the first
         hit already respects them rather than being sent and retracted.
         Storage is denied until consent is granted; the analytics ping still
         happens in cookieless mode, which is enough to see traffic without
         storing anything on the visitor's device. --}}
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}

        gtag('consent', 'default', {
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            analytics_storage: 'denied',
            wait_for_update: 500,
        });

        gtag('js', new Date());

        @if ($ga4)
            gtag('config', @json($ga4), { anonymize_ip: true });
        @endif

        @if ($ads)
            gtag('config', @json($ads));
        @endif

        // Read by resources/js/app.js when firing conversions. Empty labels
        // simply mean no Ads conversion is sent.
        window.__adsConversions = {
            call: @json($ads && config('site.google_ads_call_label') ? $ads.'/'.config('site.google_ads_call_label') : null),
            whatsapp: @json($ads && config('site.google_ads_whatsapp_label') ? $ads.'/'.config('site.google_ads_whatsapp_label') : null),
        };
    </script>

    {{-- async so the tag never blocks first paint. --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4 ?: $ads }}"></script>
@endif
