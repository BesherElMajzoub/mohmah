@props(['seo'])

<!doctype html>
{{-- lang/dir are set on <html>, not toggled by CSS. The document *is* Arabic
     and right-to-left; this is what makes screen readers announce it
     correctly and lets every layout use logical properties. --}}
<html lang="ar-SA" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-seo.meta :seo="$seo" />

    <link rel="icon" href="{{ asset('brand/favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('brand/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#102E2A">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <x-analytics.head />

    {{-- Site-wide entities. Emitted once here rather than per page so the
         organisation node has a single stable @id that page-level schema
         can reference instead of restating it. --}}
    <script type="application/ld+json">@json($organizationSchema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)</script>
    <script type="application/ld+json">@json($websiteSchema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)</script>

    @stack('schema')
</head>
<body class="bg-ivory text-charcoal antialiased">
    {{-- First stop for keyboard and screen-reader users. Visually hidden
         until focused, then rendered as a normal button. --}}
    <a href="#main"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:start-4 focus:z-[100]
              focus:rounded-sm focus:bg-ink focus:px-5 focus:py-3 focus:text-ivory">
        تخطَّ إلى المحتوى الرئيسي
    </a>

    <x-site.header />

    <main id="main">
        {{ $slot }}
    </main>

    <x-site.footer />

    {{-- Floating call / WhatsApp actions, at every breakpoint. Sits above the
         iOS safe-area inset. --}}
    <x-site.floating-actions />
</body>
</html>
