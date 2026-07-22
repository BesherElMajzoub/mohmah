@php
    use App\Support\Seo\Schema;
    $schema = app(Schema::class);
@endphp

<x-layouts.public :seo="$seo">

@push('schema')
    <script type="application/ld+json">{!! Schema::encode($schema->breadcrumbs($seo->breadcrumbs)) !!}</script>
@endpush

<section class="border-b border-stone bg-ivory-dim">
    <div class="mx-auto max-w-7xl px-5 py-10 lg:px-8">
        <x-ui.breadcrumbs :items="$seo->breadcrumbs" />

        <div class="mt-8 max-w-3xl">
            <h1 class="font-display text-3xl leading-tight text-ink md:text-5xl">
                الخدمات القانونية
            </h1>
            <p class="mt-6 text-lg leading-relaxed text-charcoal-soft">
                يعمل المكتب في أربعة مجالات مترابطة، ويتيح الجمع بينها متابعة المسألة الواحدة
                عبر مراحلها دون إحالة العميل إلى جهات متعددة.
            </p>
        </div>
    </div>
</section>

<div class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-20">
    @foreach ($categories as $category)
        @continue($category->services->isEmpty())

        {{-- The anchor id matches ServiceCategory::path(), so a link to a
             pillar scrolls to its block rather than to a thin standalone
             category page that would compete with these listings. --}}
        <section id="{{ $category->slug }}" @class(['scroll-mt-28', 'mt-20' => ! $loop->first])>
            <div class="flex flex-wrap items-baseline justify-between gap-4 border-b border-stone pb-5">
                <h2 class="font-display text-2xl text-ink md:text-3xl">{{ $category->title }}</h2>
                <p class="text-sm text-charcoal-soft num">{{ $category->services->count() }} خدمات</p>
            </div>

            @if ($category->intro)
                <p class="mt-5 max-w-3xl leading-relaxed text-charcoal-soft">{{ $category->intro }}</p>
            @endif

            <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($category->services as $service)
                    <x-ui.service-card :service="$service" />
                @endforeach
            </div>
        </section>
    @endforeach

    @if ($categories->every(fn ($c) => $c->services->isEmpty()))
        <p class="text-charcoal-soft">لا توجد خدمات منشورة حالياً.</p>
    @endif
</div>

<x-ui.cta-band placement="services_index_cta" />

</x-layouts.public>
