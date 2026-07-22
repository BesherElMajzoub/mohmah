@php
    use App\Support\Content;
    use App\Support\Seo\Schema;

    $schema = app(Schema::class);
    $sections = $page->sections ?? [];
@endphp

<x-layouts.public :seo="$seo">

@push('schema')
    <script type="application/ld+json">{!! Schema::encode($schema->person($page)) !!}</script>
    <script type="application/ld+json">{!! Schema::encode($schema->breadcrumbs($breadcrumbs)) !!}</script>
@endpush

<section class="bg-ink text-ivory on-dark">
    <div class="mx-auto max-w-7xl px-5 py-10 lg:px-8">
        <x-ui.breadcrumbs :items="$breadcrumbs" class="[&_*]:!text-ivory/60 [&_[aria-current]]:!text-ivory" />

        <div class="mt-10 grid items-end gap-12 pb-6 lg:grid-cols-[1fr_20rem]">
            <div class="max-w-2xl">
                <p class="flex items-center gap-3 text-sm text-gold-soft">
                    <span class="h-px w-8 bg-gold" aria-hidden="true"></span>
                    {{ config('site.city') }} — {{ config('site.country') }}
                </p>

                <h1 class="mt-5 font-display text-4xl leading-tight text-ivory md:text-5xl">
                    {{ $page->h1 }}
                </h1>

                @if ($page->intro)
                    <p class="mt-6 text-lg leading-relaxed text-ivory/80">
                        {{ Content::publicText($page->intro) }}
                    </p>
                @endif
            </div>

            {{-- The approved portrait of the lawyer. --}}
            <div class="relative aspect-[4/5] w-full max-w-xs overflow-hidden rounded-sm border border-gold/30">
                <div class="grid-motif absolute inset-0 opacity-60" aria-hidden="true"></div>

                {{-- Cropped from the bottom, never the top — the source is
                     taller than this 4:5 frame and a bottom anchor would cut
                     the face off. --}}
                <img src="{{ asset('brand/lawyer-portrait-500.webp') }}"
                     srcset="{{ asset('brand/lawyer-portrait-500.webp') }} 500w,
                             {{ asset('brand/lawyer-portrait-750.webp') }} 750w"
                     sizes="20rem"
                     alt="المحامي ريان الجهني"
                     width="500"
                     height="782"
                     fetchpriority="high"
                     decoding="async"
                     class="absolute inset-0 h-full w-full object-cover object-top">
            </div>
        </div>
    </div>
</section>

<div class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-20">
    <div class="grid gap-14 lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-20">
        <div class="min-w-0">
            @if (filled($page->body))
                <div class="prose-legal max-w-none">
                    {!! Content::public($page->body) !!}
                </div>
            @endif

            @foreach ($sections as $section)
                @continue(empty($section['items']))
                <section class="mt-14">
                    <h2 class="font-display text-2xl text-ink">{{ $section['title'] ?? '' }}</h2>
                    <ul class="mt-6 space-y-4">
                        @foreach ($section['items'] as $item)
                            <li class="rule-gold ps-5 leading-relaxed text-charcoal">{{ $item }}</li>
                        @endforeach
                    </ul>
                </section>
            @endforeach

            {{-- Areas of focus, linked to the actual service pages rather
                 than restated as prose. --}}
            @if ($focusServices->isNotEmpty())
                <section class="mt-14">
                    <h2 class="font-display text-2xl text-ink">الخدمات التي يقدّمها المكتب</h2>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        @foreach ($focusServices as $service)
                            <a href="{{ $service->href() }}"
                               class="group flex items-center justify-between gap-3 rounded-sm border border-stone
                                      p-4 transition-colors hover:border-gold">
                                <span class="text-[0.95rem] text-charcoal group-hover:text-gold-deep">
                                    {{ $service->title }}
                                </span>
                                <span class="text-gold" aria-hidden="true">←</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="space-y-10 lg:sticky lg:top-28 lg:self-start">
            <div class="rounded-sm border border-stone bg-ivory-dim p-6">
                <h2 class="font-display text-base text-ink">الصفات المهنية</h2>
                <ul class="mt-4 space-y-3">
                    @foreach (config('site.licenses') as $license)
                        <li>
                            <p class="text-xs text-charcoal-soft">{{ $license['label'] }}</p>
                            <p class="font-display text-lg text-ink" dir="ltr" style="text-align: start;">
                                {{ $license['number'] }}
                            </p>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('licenses') }}"
                   class="mt-4 inline-block text-sm text-gold-deep underline-offset-4 hover:underline">
                    تفاصيل التراخيص ←
                </a>
            </div>

            <div class="rounded-sm bg-ink p-6 text-ivory on-dark">
                <p class="font-display text-lg">تواصل مع المكتب</p>
                <div class="mt-5 flex flex-col gap-2.5">
                    <x-cta.call placement="about_sidebar" variant="on-dark" label="اتصل الآن" class="!w-full" />
                    <x-cta.whatsapp placement="about_sidebar" variant="on-dark" label="واتساب" class="!w-full" />
                </div>
            </div>
        </aside>
    </div>
</div>

<x-ui.cta-band placement="about_footer_cta" />

</x-layouts.public>
