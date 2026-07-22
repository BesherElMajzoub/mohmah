{{-- The header is always ink, on every page.

     It previously went transparent on the homepage to overlay the hero, but a
     sticky element sits inside the document flow rather than on top of it — so
     "transparent" resolved to the ivory body background and every nav item,
     all of which are text-ivory, became white-on-cream and invisible.

     Keeping it solid removes that whole class of failure, and because the hero
     is ink too the two surfaces merge into one continuous dark field at the top
     of the homepage: it still reads as an overlay, but contrast can never
     break. --}}

<header data-site-header
        x-data="{ mobileOpen: false, openMenu: null }"
        @keydown.escape.window="mobileOpen = false; openMenu = null"
        class="sticky top-0 z-50 bg-ink on-dark transition-shadow duration-300
               [&.is-scrolled]:shadow-2xl [&.is-scrolled]:shadow-ink-950/50">

    {{-- Utility strip. Carries the two facts a prospective client scans for
         first — where the office is, and its number — and collapses on scroll
         so the sticky bar stays compact while reading. --}}
    <div class="hidden border-b border-ink-600/50 bg-ink-950 transition-all duration-300 lg:block
                [.is-scrolled_&]:pointer-events-none [.is-scrolled_&]:max-h-0
                [.is-scrolled_&]:overflow-hidden [.is-scrolled_&]:opacity-0
                max-h-12">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-2.5 lg:px-8">
            <p class="flex items-center gap-2.5 text-xs text-ivory/55">
                <span class="h-px w-6 bg-gold/70" aria-hidden="true"></span>
                {{ config('site.city') }} — {{ config('site.country') }}
            </p>

            <p class="flex items-center gap-2.5 text-xs text-ivory/55">
                <span>محاماة · توثيق · تحكيم · تسجيل عيني</span>
                <span class="text-ivory/20" aria-hidden="true">|</span>
                <a href="{{ config('site.tel_href') }}"
                   data-track="call"
                   data-placement="header_utility"
                   class="num text-gold-soft transition-colors hover:text-gold">{{ config('site.phone_display') }}</a>
            </p>
        </div>
    </div>

    <div class="mx-auto flex max-w-7xl items-center gap-6 px-5 py-3.5 transition-all duration-300 lg:px-8
                [.is-scrolled_&]:py-2.5">

        {{-- Brand --}}
        <a href="{{ route('home') }}"
           class="group flex shrink-0 items-center gap-3.5"
           aria-label="{{ config('site.name') }} — الصفحة الرئيسية">
            {{-- alt="" because the adjacent text already names the office;
                 announcing the mark too would read the name twice. Intrinsic
                 dimensions are stated so the header reserves its space and
                 does not shift as the image decodes. --}}
            <img src="{{ asset('brand/logo-mark.png') }}"
                 alt=""
                 width="121" height="240"
                 class="h-12 w-auto transition-all duration-300 [.is-scrolled_&]:h-10">

            {{-- A gold hairline between mark and name — the same linework the
                 rest of the identity is built from. --}}
            <span class="hidden h-9 w-px bg-gold/35 sm:block" aria-hidden="true"></span>

            <span class="hidden font-display text-base leading-tight sm:block">
                <span class="block text-ivory/70 text-[0.8rem]">مكتب المحامي</span>
                <span class="block text-lg text-ivory transition-colors group-hover:text-gold-soft">ريان الجهني</span>
            </span>
        </a>

        {{-- Desktop navigation ------------------------------------------- --}}
        <nav class="ms-auto hidden items-center lg:flex" aria-label="التنقل الرئيسي">
            <x-site.nav-link :href="route('home')" :active="request()->routeIs('home')">
                الرئيسية
            </x-site.nav-link>

            @foreach ($navCategories as $category)
                @continue($category->services->isEmpty())

                {{-- Each pillar opens its own panel. Hover opens it for mouse
                     users; focus-within keeps it open for keyboard users, so
                     the menu is operable by tab alone. --}}
                <div class="relative"
                     @mouseenter="openMenu = {{ $category->id }}"
                     @mouseleave="openMenu = null">
                    <button type="button"
                            @click="openMenu = openMenu === {{ $category->id }} ? null : {{ $category->id }}"
                            :aria-expanded="openMenu === {{ $category->id }} ? 'true' : 'false'"
                            class="group/nav relative flex items-center gap-1.5 px-3.5 py-2.5 font-display
                                   text-[0.95rem] text-ivory/85 transition-colors hover:text-gold-soft"
                            :class="openMenu === {{ $category->id }} && 'text-gold-soft'">
                        {{ $category->menuLabel() }}
                        <svg class="size-3.5 transition-transform duration-200"
                             :class="openMenu === {{ $category->id }} && 'rotate-180'"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" aria-hidden="true">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>

                        {{-- Gold underline growing from the centre. Matches the
                             active treatment on the plain nav links. --}}
                        <span class="absolute inset-x-3.5 bottom-1 h-px origin-center scale-x-0 bg-gold
                                     transition-transform duration-300 ease-calm group-hover/nav:scale-x-100"
                              :class="openMenu === {{ $category->id }} && 'scale-x-100'"
                              aria-hidden="true"></span>
                    </button>

                    <div x-show="openMenu === {{ $category->id }}"
                         x-transition.opacity.duration.150ms
                         x-cloak
                         class="absolute end-0 top-full w-[30rem] pt-3">
                        <div class="rounded-sm border-t-2 border-gold bg-ivory p-6 shadow-2xl">
                            @if ($category->intro)
                                <p class="mb-4 border-b border-stone pb-4 text-sm text-charcoal-soft">
                                    {{ $category->intro }}
                                </p>
                            @endif

                            <ul class="grid grid-cols-2 gap-x-6 gap-y-1">
                                @foreach ($category->services as $service)
                                    <li>
                                        <a href="{{ $service->href() }}"
                                           class="block rounded-sm px-3 py-2 text-[0.925rem] text-charcoal
                                                  transition-colors hover:bg-stone-soft hover:text-ink">
                                            {{ $service->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach

            <x-site.nav-link :href="route('about')" :active="request()->routeIs('about')">
                عن المحامي
            </x-site.nav-link>

            <x-site.nav-link :href="route('posts.index')" :active="request()->routeIs('posts.*')">
                المدونة
            </x-site.nav-link>

            <x-site.nav-link :href="route('contact')" :active="request()->routeIs('contact')">
                اتصل بنا
            </x-site.nav-link>
        </nav>

        {{-- Desktop conversion actions ------------------------------------ --}}
        <div class="hidden shrink-0 items-center gap-3 lg:flex">
            <x-cta.whatsapp placement="header" variant="on-dark" class="!px-5 !py-2.5 !text-sm" />
            <x-cta.call placement="header" variant="on-dark" label="اتصل الآن" class="!px-5 !py-2.5 !text-sm" />
        </div>

        {{-- Mobile menu toggle -------------------------------------------- --}}
        <button type="button"
                @click="mobileOpen = !mobileOpen"
                :aria-expanded="mobileOpen ? 'true' : 'false'"
                aria-controls="mobile-nav"
                class="ms-auto rounded-sm p-2 text-ivory lg:hidden">
            <span class="sr-only">القائمة</span>
            <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
                <path x-show="!mobileOpen" d="M4 7h16M4 12h16M4 17h16"/>
                <path x-show="mobileOpen" x-cloak d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>
    </div>

    {{-- Mobile navigation ------------------------------------------------
         Deliberately a plain accordion rather than a mega menu. On a phone,
         speed and thumb reach matter more than showing every service at
         once. --}}
    <div id="mobile-nav"
         x-show="mobileOpen"
         x-cloak
         x-transition.origin.top
         class="border-t border-ink-600 bg-ink lg:hidden">
        <nav class="max-h-[75vh] overflow-y-auto px-5 py-4" aria-label="التنقل الرئيسي">
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('home') }}" class="block rounded-sm px-3 py-3 font-display text-ivory">
                        الرئيسية
                    </a>
                </li>

                @foreach ($navCategories as $category)
                    @continue($category->services->isEmpty())

                    <li x-data="{ open: false }" class="border-t border-ink-600/60">
                        <button type="button"
                                @click="open = !open"
                                :aria-expanded="open ? 'true' : 'false'"
                                class="flex w-full items-center justify-between rounded-sm px-3 py-3
                                       text-start font-display text-ivory">
                            {{ $category->menuLabel() }}
                            <svg class="size-4 transition-transform" :class="open && 'rotate-180'"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>

                        <ul x-show="open" x-collapse x-cloak class="pb-2 ps-3">
                            @foreach ($category->services as $service)
                                <li>
                                    <a href="{{ $service->href() }}"
                                       class="block rounded-sm px-3 py-2.5 text-[0.925rem] text-ivory/75">
                                        {{ $service->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach

                <li class="border-t border-ink-600/60">
                    <a href="{{ route('about') }}" class="block rounded-sm px-3 py-3 font-display text-ivory">
                        عن المحامي
                    </a>
                </li>
                <li class="border-t border-ink-600/60">
                    <a href="{{ route('licenses') }}" class="block rounded-sm px-3 py-3 font-display text-ivory">
                        التراخيص والاعتمادات
                    </a>
                </li>
                <li class="border-t border-ink-600/60">
                    <a href="{{ route('posts.index') }}" class="block rounded-sm px-3 py-3 font-display text-ivory">
                        المدونة
                    </a>
                </li>
                <li class="border-t border-ink-600/60">
                    <a href="{{ route('contact') }}" class="block rounded-sm px-3 py-3 font-display text-ivory">
                        اتصل بنا
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>
