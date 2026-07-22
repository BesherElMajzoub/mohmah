@php
    use App\Support\Content;
@endphp

<x-layouts.public :seo="$seo">

{{-- Privacy and terms share one template: same structure, different copy,
     both noindex. No BreadcrumbList schema here — the pages are noindex, so
     structured data would describe something that is not in the index. --}}

<section class="border-b border-stone bg-ivory-dim">
    <div class="mx-auto max-w-3xl px-5 py-10 lg:px-8">
        <x-ui.breadcrumbs :items="$breadcrumbs" />

        <h1 class="mt-8 font-display text-3xl leading-tight text-ink md:text-4xl">{{ $page->h1 }}</h1>

        @if ($page->intro)
            <p class="mt-5 leading-relaxed text-charcoal-soft">{{ Content::publicText($page->intro) }}</p>
        @endif

        <p class="mt-6 text-sm text-charcoal-soft">
            آخر تحديث:
            <time datetime="{{ $page->updated_at?->toDateString() }}">
                {{ $page->updated_at?->translatedFormat('j F Y') }}
            </time>
        </p>
    </div>
</section>

<div class="mx-auto max-w-3xl px-5 py-16 lg:px-8">
    <div class="prose-legal">
        {!! Content::public($page->body) !!}
    </div>
</div>

</x-layouts.public>
