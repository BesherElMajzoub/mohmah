@php
    use App\Support\Content;
    use App\Support\Seo\Schema;
    $schema = app(Schema::class);
@endphp

<x-layouts.public :seo="$seo">

@push('schema')
    <script type="application/ld+json">{!! Schema::encode($schema->breadcrumbs($breadcrumbs)) !!}</script>
@endpush

<section class="border-b border-stone bg-ivory-dim">
    <div class="mx-auto max-w-7xl px-5 py-10 lg:px-8">
        <x-ui.breadcrumbs :items="$breadcrumbs" />

        <div class="mt-8 max-w-3xl">
            <h1 class="font-display text-3xl leading-tight text-ink md:text-5xl">{{ $page->h1 }}</h1>

            @if ($page->intro)
                <p class="mt-6 text-lg leading-relaxed text-charcoal-soft">
                    {{ Content::publicText($page->intro) }}
                </p>
            @endif
        </div>
    </div>
</section>

<div class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-20">
    <div class="grid gap-14 lg:grid-cols-[minmax(0,1fr)_18rem] lg:gap-20">
        <div class="prose-legal min-w-0 max-w-none">
            {!! Content::public($page->body) !!}
        </div>

        <aside class="lg:sticky lg:top-28 lg:self-start">
            <div class="rounded-sm bg-ink p-6 text-ivory on-dark">
                <p class="font-display text-lg">اطلب مناقشة أولية</p>
                <p class="mt-3 text-sm leading-relaxed text-ivory/70">
                    اعرض تفاصيل حالتك ومستنداتها لتحديد المسار المناسب.
                </p>
                <div class="mt-5 flex flex-col gap-2.5">
                    <x-cta.call placement="methodology_sidebar" variant="on-dark" label="اتصل بالمكتب" class="!w-full" />
                    <x-cta.whatsapp placement="methodology_sidebar" variant="on-dark" label="واتساب" class="!w-full" />
                </div>
            </div>
        </aside>
    </div>
</div>

<x-ui.cta-band placement="methodology_footer_cta" />

</x-layouts.public>
