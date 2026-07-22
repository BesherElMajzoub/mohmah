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
