@php
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
            <h1 class="font-display text-3xl leading-tight text-ink md:text-5xl">
                {{ $activeCategory?->title ?? 'المدونة' }}
            </h1>
            <p class="mt-6 text-lg leading-relaxed text-charcoal-soft">
                {{ $activeCategory?->intro ?? 'مقالات تحليلية موجّهة لأصحاب الشركات والمستثمرين وملّاك العقارات، تتناول المسائل التي يترتب عليها أثر تجاري أو مالي مباشر.' }}
            </p>
        </div>

        {{-- Category filter --}}
        <nav class="mt-8 flex flex-wrap gap-2" aria-label="أقسام المدونة">
            <a href="{{ route('posts.index') }}"
               @if (! $activeCategory) aria-current="page" @endif
               @class([
                   'rounded-sm border px-4 py-2 text-sm transition-colors',
                   'border-ink bg-ink text-ivory' => ! $activeCategory,
                   'border-stone text-charcoal hover:border-gold' => $activeCategory,
               ])>
                الكل
            </a>

            @foreach ($categories as $category)
                <a href="{{ $category->href() }}"
                   @if ($activeCategory?->is($category)) aria-current="page" @endif
                   @class([
                       'rounded-sm border px-4 py-2 text-sm transition-colors',
                       'border-ink bg-ink text-ivory' => $activeCategory?->is($category),
                       'border-stone text-charcoal hover:border-gold' => ! $activeCategory?->is($category),
                   ])>
                    {{ $category->title }}
                </a>
            @endforeach
        </nav>
    </div>
</section>

<div class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-20">
    @if ($posts->isEmpty())
        {{-- An honest empty state. No placeholder articles are ever seeded to
             make the section look populated. --}}
        <div class="mx-auto max-w-xl rounded-sm border border-stone bg-ivory-dim p-10 text-center">
            <p class="font-display text-xl text-ink">لا توجد مقالات منشورة بعد</p>
            <p class="mt-4 leading-relaxed text-charcoal-soft">
                لمناقشة مسألة قانونية بعينها، تواصل مع المكتب مباشرة.
            </p>
            <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                <x-cta.call placement="blog_empty" label="اتصل بالمكتب" />
                <x-cta.whatsapp placement="blog_empty" />
            </div>
        </div>
    @else
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <article class="flex flex-col">
                    @if ($post->coverImage)
                        <a href="{{ $post->href() }}" class="block overflow-hidden rounded-sm" tabindex="-1" aria-hidden="true">
                            <img src="{{ $post->coverImage->url() }}"
                                 alt="{{ $post->coverImage->alt_ar }}"
                                 width="{{ $post->coverImage->width }}"
                                 height="{{ $post->coverImage->height }}"
                                 loading="lazy"
                                 decoding="async"
                                 class="aspect-[16/10] w-full object-cover transition-transform duration-500 hover:scale-105">
                        </a>
                    @endif

                    <p @class(['text-sm text-gold-deep', 'mt-5' => $post->coverImage])>
                        {{ $post->category->title }}
                    </p>

                    <h2 class="mt-2 font-display text-xl leading-snug">
                        <a href="{{ $post->href() }}" class="text-ink hover:text-gold-deep">
                            {{ $post->title }}
                        </a>
                    </h2>

                    @if ($post->excerpt)
                        <p class="mt-3 flex-1 leading-relaxed text-charcoal-soft">
                            {{ Str::limit($post->excerpt, 150) }}
                        </p>
                    @endif

                    <p class="mt-5 text-xs text-charcoal-soft">
                        <time datetime="{{ $post->published_at?->toDateString() }}">
                            {{ $post->published_at?->translatedFormat('j F Y') }}
                        </time>
                        @if ($post->reading_minutes)
                            <span aria-hidden="true">·</span> قراءة {{ $post->reading_minutes }} دقائق
                        @endif
                    </p>
                </article>
            @endforeach
        </div>

        <div class="mt-16">
            {{ $posts->links() }}
        </div>
    @endif
</div>

<x-ui.cta-band placement="blog_footer_cta" />

</x-layouts.public>
