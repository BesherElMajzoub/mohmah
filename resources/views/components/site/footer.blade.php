{{-- Contact details render only when the client has entered real values.
     Every block below is behind a $settings->filled() guard for exactly that
     reason — an invented address or a placeholder "9am–5pm" on a law office
     footer is a factual claim, and a visitor who drives to it is worse off
     than one who called. --}}

<footer class="bg-ink text-ivory/80 on-dark">
    <div class="mx-auto max-w-7xl px-5 py-12 lg:px-8 lg:py-14">

        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.6fr_1fr_1fr]">

            {{-- Brand + contact ------------------------------------------- --}}
            <div>
                <img src="{{ asset('brand/logo-mark.png') }}" alt="" width="121" height="240" class="h-12 w-auto">

                <p class="mt-4 font-display text-lg text-ivory">{{ config('site.name') }}</p>

                <p class="mt-2.5 max-w-xs text-sm leading-relaxed">
                    خدمات قانونية للشركات والمستثمرين وملّاك العقارات
                    في {{ config('site.city') }}، {{ config('site.country') }}.
                </p>

                <div class="mt-5 space-y-2 text-sm">
                    <a href="{{ config('site.tel_href') }}"
                       data-track="call"
                       data-placement="footer"
                       class="flex items-center gap-2.5 transition-colors hover:text-gold-soft">
                        <span class="text-gold">هاتف</span>
                        <span class="num">{{ config('site.phone_display') }}</span>
                    </a>

                    <a href="{{ config('site.whatsapp_href') }}"
                       target="_blank" rel="noopener"
                       data-track="whatsapp"
                       data-placement="footer"
                       class="flex items-center gap-2.5 transition-colors hover:text-gold-soft">
                        <span class="text-gold">واتساب</span>
                        <span class="num">{{ config('site.phone_display') }}</span>
                    </a>

                    @if ($settings->filled('office_email'))
                        <a href="mailto:{{ $settings->get('office_email') }}"
                           class="flex items-center gap-2.5 transition-colors hover:text-gold-soft">
                            <span class="text-gold">البريد</span>
                            <span dir="ltr">{{ $settings->get('office_email') }}</span>
                        </a>
                    @endif
                </div>

                @if ($settings->filled('office_address'))
                    <address class="mt-5 text-sm not-italic leading-relaxed">
                        {{ $settings->get('office_address') }}
                        @if ($settings->filled('map_url'))
                            <a href="{{ $settings->get('map_url') }}"
                               target="_blank" rel="noopener"
                               class="mt-1 block text-gold-soft underline underline-offset-4">
                                عرض الموقع على الخريطة
                            </a>
                        @endif
                    </address>
                @endif

                @if ($settings->filled('office_hours'))
                    <p class="mt-4 text-sm">
                        <span class="text-gold">أوقات العمل:</span>
                        {{ $settings->get('office_hours') }}
                    </p>
                @endif
            </div>

            {{-- Practice areas ---------------------------------------------
                 The four pillars only. Listing all twenty-odd services here
                 made the footer taller than most of the pages above it, and
                 nobody navigates a law firm from a footer link farm — the
                 mega menu and the services index both do that job better. --}}
            <div>
                <h2 class="font-display text-base text-gold-soft">مجالات العمل</h2>
                <ul class="mt-5 space-y-2 text-sm">
                    @foreach ($navCategories as $category)
                        @continue($category->services->isEmpty())
                        <li>
                            <a href="{{ $category->path() }}" class="transition-colors hover:text-gold-soft">
                                {{ $category->menuLabel() }}
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('services.index') }}" class="text-gold-soft/80 transition-colors hover:text-gold-soft">
                            جميع الخدمات
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Office + legal -------------------------------------------- --}}
            <div>
                <h2 class="font-display text-base text-gold-soft">المكتب</h2>
                <ul class="mt-5 space-y-2 text-sm">
                    <li><a href="{{ route('about') }}" class="transition-colors hover:text-gold-soft">عن المحامي</a></li>
                    <li><a href="{{ route('licenses') }}" class="transition-colors hover:text-gold-soft">التراخيص والاعتمادات</a></li>
                    <li><a href="{{ route('methodology') }}" class="transition-colors hover:text-gold-soft">منهجية العمل</a></li>
                    <li><a href="{{ route('posts.index') }}" class="transition-colors hover:text-gold-soft">المدونة</a></li>
                    <li><a href="{{ route('contact') }}" class="transition-colors hover:text-gold-soft">تواصل معنا</a></li>
                </ul>

                {{-- Social links appear only once real, approved accounts are
                     entered in settings. No empty icon row, no dead links. --}}
                @php $social = array_filter((array) $settings->get('social_links', [])); @endphp
                @if ($social !== [])
                    <h2 class="mt-8 font-display text-base text-gold-soft">حسابات المكتب</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($social as $label => $url)
                            <li>
                                <a href="{{ $url }}" target="_blank" rel="noopener me"
                                   class="transition-colors hover:text-gold-soft">{{ $label }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- Licence strip ------------------------------------------------- --}}
        <div class="mt-12 border-t border-ink-600 pt-7">
            <ul class="flex flex-wrap gap-x-8 gap-y-2.5 text-xs">
                @foreach (config('site.licenses') as $license)
                    <li class="flex items-center gap-2">
                        <span class="text-ivory/60">{{ $license['label'] }}</span>
                        <span class="font-display text-gold-soft">{{ $license['number'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Disclaimer and legal links share one row: both are fine print,
             and separating them cost two more full-width bands. --}}
        <div class="mt-7 flex flex-col gap-5 border-t border-ink-600 pt-7 text-xs text-ivory/45 lg:flex-row lg:gap-12">
            <p class="max-w-2xl leading-relaxed">
                المحتوى المنشور في هذا الموقع ذو طبيعة تعريفية عامة، ولا يُعدّ استشارة قانونية
                ولا ينشئ علاقة توكيل بين الزائر والمكتب. تختلف المعالجة القانونية باختلاف وقائع
                كل حالة ووثائقها.
            </p>

            <div class="flex flex-col gap-3 lg:ms-auto lg:items-end lg:text-end">
                <ul class="flex gap-6">
                    <li><a href="{{ route('privacy') }}" class="hover:text-gold-soft">سياسة الخصوصية</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-gold-soft">الشروط والأحكام</a></li>
                </ul>
                <p class="text-ivory/35">© {{ now()->year }} {{ config('site.name') }}. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </div>
</footer>
