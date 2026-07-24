@props(['seo'])

{{-- The only place in the application that writes head metadata. Every
     indexable page therefore gets a unique title, description and canonical
     by construction rather than by discipline. --}}

<title>{{ $seo->documentTitle() }}</title>

@if ($seo->description)
    <meta name="description" content="{{ $seo->description }}">
@endif

<link rel="canonical" href="{{ $seo->canonicalUrl() }}">
<meta name="robots" content="{{ $seo->robots() }}">

@if (config('site.search_console_token'))
    <meta name="google-site-verification" content="{{ config('site.search_console_token') }}">
@endif

{{-- Open Graph --}}
<meta property="og:type" content="{{ $seo->type }}">
<meta property="og:locale" content="ar_SA">
<meta property="og:site_name" content="{{ config('site.name') }}">
<meta property="og:title" content="{{ $seo->documentTitle() }}">
@if ($seo->description)
    <meta property="og:description" content="{{ $seo->description }}">
@endif
<meta property="og:url" content="{{ $seo->canonicalUrl() }}">
<meta property="og:image" content="{{ $seo->imageUrl() }}">

{{-- Dimensions let a scraper reserve the card layout before it has fetched
     the image, which is what stops the first share of a new URL rendering as
     a small-thumbnail card. Only emitted for uploads, where the size is
     known — the default OG image is not a Media row. --}}
@if ($seo->image?->width && $seo->image?->height)
    <meta property="og:image:width" content="{{ $seo->image->width }}">
    <meta property="og:image:height" content="{{ $seo->image->height }}">
@endif
@if ($seo->image?->alt_ar)
    <meta property="og:image:alt" content="{{ $seo->image->alt_ar }}">
@endif

@if ($seo->publishedTime)
    <meta property="article:published_time" content="{{ $seo->publishedTime }}">
@endif
@if ($seo->modifiedTime)
    <meta property="article:modified_time" content="{{ $seo->modifiedTime }}">
@endif

{{-- X/Twitter. summary_large_image because every page has a real OG image. --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->documentTitle() }}">
@if ($seo->description)
    <meta name="twitter:description" content="{{ $seo->description }}">
@endif
<meta name="twitter:image" content="{{ $seo->imageUrl() }}">
@if ($seo->image?->alt_ar)
    <meta name="twitter:image:alt" content="{{ $seo->image->alt_ar }}">
@endif
