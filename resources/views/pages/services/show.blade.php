@php
    use App\Support\Content;
    use App\Support\Seo\Schema;

    $schema = app(Schema::class);
@endphp

<x-layouts.public :seo="$seo">

@push('schema')
    <script type="application/ld+json">{!! Schema::encode($schema->service($service)) !!}</script>
    <script type="application/ld+json">{!! Schema::encode($schema->breadcrumbs($breadcrumbs)) !!}</script>
@endpush

{{-- Page header ------------------------------------------------------- --}}
<section class="border-b border-stone bg-ivory-dim">
    <div class="mx-auto max-w-7xl px-5 py-10 lg:px-8">
        <x-ui.breadcrumbs :items="$breadcrumbs" />

        <div class="mt-8 max-w-3xl">
            <p class="flex items-center gap-3 text-sm text-gold-deep">
                <span class="h-px w-8 bg-gold" aria-hidden="true"></span>
                {{ $service->category->title }}
            </p>

            {{-- The one H1 on the page. --}}
            <h1 class="mt-5 font-display text-3xl leading-tight text-ink md:text-5xl">
                {{ $service->h1 }}
            </h1>

            @if ($service->summary)
                <p class="mt-6 text-lg leading-relaxed text-charcoal-soft md:text-xl">
                    {{ $service->summary }}
                </p>
            @endif

            <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                <x-cta.call placement="service_header" label="تحدث مع المكتب" />
                <x-cta.whatsapp placement="service_header" label="واتساب" />
            </div>
        </div>
    </div>
</section>

<div class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-20">
    <div class="grid gap-14 lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-20">

        {{-- Main column --------------------------------------------------- --}}
        <div class="min-w-0">

            @if (filled($service->overview))
                <div class="prose-legal max-w-none">
                    {!! Content::public($service->overview) !!}
                </div>
            @endif

            {{-- Who this is for --}}
            @if (! empty($service->audience))
                <section class="mt-16">
                    <h2 class="font-display text-2xl text-ink">لمن هذه الخدمة</h2>
                    <ul class="mt-6 space-y-4">
                        @foreach ($service->audience as $item)
                            <li class="rule-gold ps-5 leading-relaxed text-charcoal">{{ $item }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Scope of work --}}
            @if (! empty($service->scope))
                <section class="mt-16">
                    <h2 class="font-display text-2xl text-ink">نطاق العمل</h2>
                    <ul class="mt-6 grid gap-px overflow-hidden rounded-sm bg-stone sm:grid-cols-2">
                        @foreach ($service->scope as $item)
                            <li class="bg-ivory p-5 text-[0.95rem] leading-relaxed text-charcoal">
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Process --}}
            @if (! empty($service->process))
                <section class="mt-16">
                    <h2 class="font-display text-2xl text-ink">كيف نعمل على هذه المسألة</h2>
                    <ol class="mt-8 space-y-8">
                        @foreach ($service->process as $index => $step)
                            <li class="flex gap-5">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full
                                             border border-gold font-display text-sm text-gold-deep num">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <p class="font-display text-lg text-ink">{{ $step['title'] ?? '' }}</p>
                                    <p class="mt-2 leading-relaxed text-charcoal-soft">{{ $step['body'] ?? '' }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            {{-- FAQs --}}
            @if (! empty($service->faqs))
                <section class="mt-16">
                    <h2 class="font-display text-2xl text-ink">أسئلة متكررة</h2>
                    <x-ui.faq :items="$service->faqs" class="mt-6" />
                </section>
            @endif

            {{-- Disclaimer --}}
            @if (filled($service->disclaimer))
                <p class="mt-14 rounded-sm border border-stone bg-stone-soft/50 p-5 text-sm leading-relaxed text-charcoal-soft">
                    {{ Content::publicText($service->disclaimer) }}
                </p>
            @endif
        </div>

        {{-- Sidebar ------------------------------------------------------- --}}
        <aside class="space-y-10 lg:sticky lg:top-28 lg:self-start">

            {{-- Relevant licences, only where factually applicable --}}
            @if ($service->licenses() !== [])
                <div class="rounded-sm border border-stone bg-ivory-dim p-6">
                    <h2 class="font-display text-base text-ink">التراخيص ذات العلاقة</h2>
                    <ul class="mt-4 space-y-3">
                        @foreach ($service->licenses() as $license)
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
                        جميع التراخيص ←
                    </a>
                </div>
            @endif

            {{-- Contact card --}}
            <div class="rounded-sm bg-ink p-6 text-ivory on-dark">
                <p class="font-display text-lg">اعرض حالتك على المكتب</p>
                <p class="mt-3 text-sm leading-relaxed text-ivory/70">
                    تواصل لمناقشة تفاصيل المسألة ومستنداتها وتحديد الخطوة التالية.
                </p>
                <div class="mt-5 flex flex-col gap-2.5">
                    <x-cta.call placement="service_sidebar" variant="on-dark" label="اتصل الآن" class="!w-full" />
                    <x-cta.whatsapp placement="service_sidebar" variant="on-dark" label="واتساب" class="!w-full" />
                </div>
                <p class="mt-4 text-center text-sm text-ivory/50 num">{{ config('site.phone_display') }}</p>
            </div>

            {{-- Related services --}}
            @if ($service->relatedServices->isNotEmpty())
                <div>
                    <h2 class="font-display text-base text-ink">خدمات ذات صلة</h2>
                    <ul class="mt-4 space-y-2.5">
                        @foreach ($service->relatedServices as $related)
                            <li>
                                <a href="{{ $related->href() }}"
                                   class="group flex items-center gap-3 text-[0.95rem] text-charcoal hover:text-gold-deep">
                                    <span class="h-px w-4 bg-gold transition-all group-hover:w-6" aria-hidden="true"></span>
                                    {{ $related->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Related articles --}}
            @if ($service->posts->isNotEmpty())
                <div>
                    <h2 class="font-display text-base text-ink">مقالات ذات صلة</h2>
                    <ul class="mt-4 space-y-4">
                        @foreach ($service->posts as $post)
                            <li>
                                <a href="{{ $post->href() }}" class="text-[0.95rem] leading-snug text-charcoal hover:text-gold-deep">
                                    {{ $post->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>
    </div>
</div>

<x-ui.cta-band placement="service_footer_cta" />

</x-layouts.public>
