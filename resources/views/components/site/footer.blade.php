{{-- Contact details render only when the client has entered real values.
     Every block below is behind a $settings->filled() guard for exactly that
     reason — an invented address or a placeholder "9am–5pm" on a law office
     footer is a factual claim, and a visitor who drives to it is worse off
     than one who called.

     The footer opens with a closing band rather than jumping straight to link
     columns. A visitor who has read to the bottom of a service page is the
     most likely to call, and meeting them with four equal-weight columns of
     links wastes that moment. The band gives them the number at a size they
     can read across a desk, then the columns follow quietly. --}}

<footer class="bg-ink text-ivory/75 on-dark">

    {{-- ============================================================
         Closing band
         ============================================================ --}}
    <div class="relative isolate overflow-hidden border-b border-ink-600">
        <div class="grid-motif absolute inset-0 opacity-30" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-7xl px-5 py-10 lg:px-8 lg:py-14">
            <div class="relative overflow-hidden rounded-3xl border border-gold/20 bg-gradient-to-br from-ink-700/50 via-ink-800/70 to-ink-950/90 p-8 shadow-2xl backdrop-blur-xl lg:p-12">
                {{-- Ambient Light Accent --}}
                <div class="pointer-events-none absolute -top-24 -left-24 size-80 rounded-full bg-gold/10 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-24 -right-24 size-80 rounded-full bg-gold-soft/5 blur-3xl" aria-hidden="true"></div>

                <div class="relative flex flex-col gap-10 lg:flex-row lg:items-center lg:justify-between lg:gap-16">

                    <div>
                        {{-- Availability Badge --}}
                        <div class="mb-5 inline-flex items-center gap-2.5 rounded-full border border-gold/25 bg-gold/10 px-3.5 py-1 text-xs text-gold-soft backdrop-blur-md">
                            <span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>استشارات قانونية فورية ومباشرة</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="relative flex size-16 shrink-0 items-center justify-center rounded-2xl border border-gold/30 bg-ink-900/60 p-2.5 shadow-md">
                                <img src="{{ asset('brand/logo-mark.png') }}"
                                     alt=""
                                     width="121" height="240"
                                     class="h-full w-auto object-contain">
                            </div>

                            <span class="h-12 w-px bg-gold/35" aria-hidden="true"></span>

                            <span class="font-display leading-tight">
                                <span class="block text-xs font-medium tracking-wide text-ivory/60">مكتب المحامي</span>
                                <span class="block text-2xl font-bold text-ivory">ريان الجهني</span>
                            </span>
                        </div>

                        <p class="mt-5 max-w-lg text-base leading-relaxed text-ivory/80">
                            خدمات قانونية متكاملة للشركات، المستثمرين، وملّاك العقارات
                            في {{ config('site.city') }}، {{ config('site.country') }}.
                        </p>
                    </div>

                    {{-- Call Action Box --}}
                    <div class="relative shrink-0 rounded-2xl border border-gold/20 bg-ink-900/75 p-6 backdrop-blur-md shadow-xl lg:w-[420px] lg:p-7">
                        <div class="flex items-center justify-between border-b border-ink-700/60 pb-3">
                            <span class="text-xs text-gold-soft font-medium">هاتف المكتب المباشر</span>
                            <span class="flex items-center gap-1.5 text-xs text-ivory/50">
                                <svg class="size-3.5 text-gold-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                سرعة في الرد
                            </span>
                        </div>

                        <p class="mt-3">
                            <a href="{{ config('site.tel_href') }}"
                               data-track="call"
                               data-placement="footer_headline"
                               class="num font-display text-3xl tracking-tight text-ivory transition-all hover:text-gold-soft md:text-4xl">
                                {{ config('site.phone_display') }}
                            </a>
                        </p>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                            <x-cta.call placement="footer_band" variant="on-dark" label="اتصل الآن"
                                        class="!w-full !px-5 !py-3.5 !text-sm shadow-md transition-transform hover:scale-[1.02]" />
                            <x-cta.whatsapp placement="footer_band" variant="on-dark" label="واتساب"
                                            class="!w-full !px-5 !py-3.5 !text-sm shadow-md transition-transform hover:scale-[1.02]" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         Navigation columns
         ============================================================ --}}
    <div class="mx-auto max-w-7xl px-5 py-12 lg:px-8 lg:py-14">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_1.4fr] lg:gap-12">

            {{-- Practice areas ---------------------------------------------
                 The four pillars only. Listing all twenty-odd services here
                 made the footer taller than most of the pages above it, and
                 nobody navigates a law firm from a footer link farm — the
                 mega menu and the services index both do that job better. --}}
            <div>
                <h2 class="font-display text-xs uppercase tracking-widest text-gold-soft">مجالات العمل</h2>

                <ul class="mt-5 space-y-2.5 text-sm">
                    @foreach ($navCategories as $category)
                        @continue($category->services->isEmpty())
                        <li>
                            <a href="{{ $category->path() }}" class="transition-colors hover:text-gold-soft">
                                {{ $category->menuLabel() }}
                            </a>
                        </li>
                    @endforeach
                    <li class="pt-1">
                        <a href="{{ route('services.index') }}"
                           class="group inline-flex items-center gap-2.5 text-gold-soft/85 transition-colors hover:text-gold-soft">
                            جميع الخدمات
                            <span class="h-px w-4 bg-gold transition-all group-hover:w-7" aria-hidden="true"></span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- The office ------------------------------------------------- --}}
            <div>
                <h2 class="font-display text-xs uppercase tracking-widest text-gold-soft">المكتب</h2>

                <ul class="mt-5 space-y-2.5 text-sm">
                    <li><a href="{{ route('about') }}" class="transition-colors hover:text-gold-soft">عن المحامي</a></li>
                    <li><a href="{{ route('licenses') }}" class="transition-colors hover:text-gold-soft">التراخيص والاعتمادات</a></li>
                    <li><a href="{{ route('methodology') }}" class="transition-colors hover:text-gold-soft">منهجية العمل</a></li>
                    <li><a href="{{ route('posts.index') }}" class="transition-colors hover:text-gold-soft">المدونة</a></li>
                    <li><a href="{{ route('contact') }}" class="transition-colors hover:text-gold-soft">تواصل معنا</a></li>
                </ul>
            </div>

            {{-- Reaching the office ---------------------------------------- --}}
            <div>
                <h2 class="font-display text-xs uppercase tracking-widest text-gold-soft">التواصل</h2>

                <ul class="mt-5 space-y-2.5 text-sm">
                    <li>
                        <a href="{{ config('site.whatsapp_href') }}"
                           target="_blank" rel="noopener"
                           data-track="whatsapp"
                           data-placement="footer"
                           class="flex items-center gap-2.5 transition-colors hover:text-gold-soft">
                            <span class="text-ivory/45">واتساب</span>
                            <span class="num">{{ config('site.phone_display') }}</span>
                        </a>
                    </li>

                    @if ($settings->filled('office_email'))
                        <li>
                            <a href="mailto:{{ $settings->get('office_email') }}"
                               class="flex items-center gap-2.5 transition-colors hover:text-gold-soft">
                                <span class="text-ivory/45">البريد</span>
                                <span dir="ltr">{{ $settings->get('office_email') }}</span>
                            </a>
                        </li>
                    @endif

                    <li class="flex items-center gap-2.5">
                        <span class="text-ivory/45">الموقع</span>
                        <span>{{ config('site.city') }}، {{ config('site.country') }}</span>
                    </li>
                </ul>

                @if ($settings->filled('office_address'))
                    <address class="mt-4 text-sm not-italic leading-relaxed">
                        {{ $settings->get('office_address') }}
                    </address>
                @endif

                @if ($settings->filled('map_url'))
                    <p class="mt-3">
                        <a href="{{ $settings->get('map_url') }}"
                           target="_blank" rel="noopener"
                           class="text-sm text-gold-soft underline underline-offset-4">
                            عرض الموقع على الخريطة
                        </a>
                    </p>
                @endif

                @if ($settings->filled('office_hours'))
                    <p class="mt-4 text-sm">
                        <span class="text-ivory/45">أوقات العمل</span>
                        <span class="mt-1 block">{{ $settings->get('office_hours') }}</span>
                    </p>
                @endif

                {{-- Social links appear only once real, approved accounts are
                     entered in settings. No empty icon row, no dead links. --}}
                @php $social = array_filter((array) $settings->get('social_links', [])); @endphp
                @if ($social !== [])
                    <ul class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                        @foreach ($social as $label => $url)
                            <li>
                                <a href="{{ $url }}" target="_blank" rel="noopener me"
                                   class="transition-colors hover:text-gold-soft">{{ $label }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Map — a column rather than a band of its own, so it costs the
                 footer no extra height on desktop. --}}
            <div class="sm:col-span-2 lg:col-span-1">
                <h2 class="font-display text-xs uppercase tracking-widest text-gold-soft">الموقع</h2>
                <x-ui.map class="mt-5" tone="dark" height="h-44 lg:h-48" />
            </div>
        </div>
    </div>

    {{-- ============================================================
         Licences — the substantive trust signal, so it gets its own
         band rather than being folded into the fine print.
         ============================================================ --}}
    <div class="border-y border-ink-600 bg-ink-950/40">
        <div class="mx-auto max-w-7xl px-5 py-7 lg:px-8">
            <ul class="grid gap-x-10 gap-y-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (config('site.licenses') as $license)
                    <li class="flex flex-col gap-1 border-s border-gold/25 ps-4">
                        <span class="text-xs text-ivory/50">{{ $license['label'] }}</span>
                        <span class="font-display text-sm text-gold-soft" dir="ltr">{{ $license['number'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- ============================================================
         Fine print
         ============================================================ --}}
    <div class="mx-auto max-w-7xl px-5 py-8 lg:px-8">
        <div class="flex flex-col gap-6 text-xs text-ivory/40 lg:flex-row lg:gap-12">
            <p class="max-w-2xl leading-relaxed">
                المحتوى المنشور في هذا الموقع ذو طبيعة تعريفية عامة، ولا يُعدّ استشارة قانونية
                ولا ينشئ علاقة توكيل بين الزائر والمكتب. تختلف المعالجة القانونية باختلاف وقائع
                كل حالة ووثائقها.
            </p>

            <div class="flex flex-col gap-3 lg:ms-auto lg:items-end lg:text-end">
                <ul class="flex gap-6">
                    <li><a href="{{ route('privacy') }}" class="transition-colors hover:text-gold-soft">سياسة الخصوصية</a></li>
                    <li><a href="{{ route('terms') }}" class="transition-colors hover:text-gold-soft">الشروط والأحكام</a></li>
                </ul>
                <p class="text-ivory/30">© {{ now()->year }} {{ config('site.name') }}. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </div>
</footer>
