@props(['title' => 'لوحة التحكم'])

<!doctype html>
<html lang="ar-SA" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- The admin must never be indexed, and unlike the public pages this is
         not conditional on environment. --}}
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title }} — لوحة التحكم</title>

    <link rel="icon" href="{{ asset('brand/favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'])
</head>
<body class="min-h-screen bg-stone-soft/40 text-charcoal" x-data="{ navOpen: false }">

<div class="flex min-h-screen flex-col lg:flex-row">

    {{-- Sidebar ------------------------------------------------------- --}}
    <aside class="bg-ink text-ivory on-dark lg:w-64 lg:shrink-0">
        <div class="flex items-center justify-between px-5 py-4 lg:block lg:px-6 lg:py-6">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('brand/logo-mark.png') }}" alt="" width="121" height="240" class="h-9 w-auto">
                <span class="font-display text-sm leading-tight">
                    لوحة التحكم<br>
                    <span class="text-gold-soft">ريان الجهني</span>
                </span>
            </a>

            <button type="button" @click="navOpen = !navOpen"
                    :aria-expanded="navOpen ? 'true' : 'false'"
                    class="rounded-sm p-2 lg:hidden">
                <span class="sr-only">القائمة</span>
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <nav x-show="navOpen" x-cloak class="px-4 pb-6 lg:!block lg:px-4" aria-label="تنقل لوحة التحكم">
            @php
                // Grouped so the sidebar reads as tasks, not as a table list.
                $groups = [
                    'المحتوى' => [
                        ['route' => 'admin.services.index', 'label' => 'الخدمات', 'match' => 'admin.services.*'],
                        ['route' => 'admin.service-categories.index', 'label' => 'مجالات الخدمات', 'match' => 'admin.service-categories.*'],
                        ['route' => 'admin.posts.index', 'label' => 'المقالات', 'match' => 'admin.posts.*'],
                        ['route' => 'admin.post-categories.index', 'label' => 'أقسام المدونة', 'match' => 'admin.post-categories.*'],
                        ['route' => 'admin.pages.index', 'label' => 'الصفحات الثابتة', 'match' => 'admin.pages.*'],
                        ['route' => 'admin.media.index', 'label' => 'مكتبة الوسائط', 'match' => 'admin.media.*'],
                    ],
                    'التحويلات' => [
                        ['route' => 'admin.clicks.index', 'label' => 'تتبع النقرات', 'match' => 'admin.clicks.*'],
                        ['route' => 'admin.submissions.index', 'label' => 'رسائل النموذج', 'match' => 'admin.submissions.*'],
                    ],
                    'الإعدادات' => [
                        ['route' => 'admin.settings.edit', 'label' => 'إعدادات الموقع', 'match' => 'admin.settings.*'],
                        ['route' => 'admin.redirects.index', 'label' => 'التحويلات (301)', 'match' => 'admin.redirects.*'],
                    ],
                ];
            @endphp

            <a href="{{ route('admin.dashboard') }}"
               @class([
                   'block rounded-sm px-3 py-2.5 text-sm transition-colors',
                   'bg-ink-600 text-ivory' => request()->routeIs('admin.dashboard'),
                   'text-ivory/75 hover:bg-ink-700 hover:text-ivory' => ! request()->routeIs('admin.dashboard'),
               ])>
                نظرة عامة
            </a>

            @foreach ($groups as $heading => $items)
                <p class="mt-6 px-3 text-xs text-gold-soft">{{ $heading }}</p>
                <ul class="mt-2 space-y-0.5">
                    @foreach ($items as $item)
                        <li>
                            <a href="{{ route($item['route']) }}"
                               @if (request()->routeIs($item['match'])) aria-current="page" @endif
                               @class([
                                   'block rounded-sm px-3 py-2.5 text-sm transition-colors',
                                   'bg-ink-600 text-ivory' => request()->routeIs($item['match']),
                                   'text-ivory/75 hover:bg-ink-700 hover:text-ivory' => ! request()->routeIs($item['match']),
                               ])>
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endforeach

            <div class="mt-8 border-t border-ink-600 pt-4">
                <a href="{{ route('home') }}" target="_blank" rel="noopener"
                   class="block rounded-sm px-3 py-2.5 text-sm text-ivory/75 hover:text-ivory">
                    عرض الموقع ↗
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-sm px-3 py-2.5 text-start text-sm text-ivory/75 hover:text-ivory">
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    {{-- Main ---------------------------------------------------------- --}}
    <div class="min-w-0 flex-1">
        <header class="border-b border-stone bg-ivory px-5 py-5 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h1 class="font-display text-2xl text-ink">{{ $title }}</h1>
                @isset($actions)
                    <div class="flex flex-wrap items-center gap-3">{{ $actions }}</div>
                @endisset
            </div>
        </header>

        <main class="px-5 py-8 lg:px-8">
            @if (session('status'))
                <div role="status" class="mb-6 rounded-sm border-s-4 border-gold bg-ivory p-4 text-sm text-charcoal">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-6 rounded-sm border-s-4 border-red-700 bg-red-50 p-4 text-sm text-red-900">
                    <p class="font-display">تعذّر الحفظ:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

</body>
</html>
