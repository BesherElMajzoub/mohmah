@php
    use App\Support\Content;
    use App\Support\Seo\Schema;
    $schema = app(Schema::class);
@endphp

<x-layouts.public :seo="$seo">

@push('schema')
    <script type="application/ld+json">{!! Schema::encode($schema->article($post)) !!}</script>
    <script type="application/ld+json">{!! Schema::encode($schema->breadcrumbs($breadcrumbs)) !!}</script>
@endpush

<article>
    <header class="border-b border-stone bg-ivory-dim">
        <div class="mx-auto max-w-3xl px-5 py-10 lg:px-8">
            <x-ui.breadcrumbs :items="$breadcrumbs" />

            <p class="mt-8 text-sm text-gold-deep">{{ $post->category->title }}</p>

            <h1 class="mt-3 font-display text-3xl leading-tight text-ink md:text-4xl">
                {{ $post->heading() }}
            </h1>

            @if ($post->excerpt)
                <p class="mt-6 text-lg leading-relaxed text-charcoal-soft">{{ $post->excerpt }}</p>
            @endif

            {{-- Byline. The reviewer is named only when one is actually
                 recorded — this is the signal that a lawyer read the piece,
                 so it must never be decorative. --}}
            <div class="mt-8 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-stone pt-6 text-sm text-charcoal-soft">
                @if ($post->author_name)
                    <span>بقلم <span class="text-charcoal">{{ $post->author_name }}</span></span>
                @endif

                @if ($post->reviewer_name)
                    <span>مراجعة <span class="text-charcoal">{{ $post->reviewer_name }}</span></span>
                @endif

                @if ($post->published_at)
                    <time datetime="{{ $post->published_at->toDateString() }}">
                        نُشر {{ $post->published_at->translatedFormat('j F Y') }}
                    </time>
                @endif

                @if ($post->content_updated_at)
                    <time datetime="{{ $post->content_updated_at->toDateString() }}">
                        حُدّث {{ $post->content_updated_at->translatedFormat('j F Y') }}
                    </time>
                @endif

                @if ($post->reading_minutes)
                    <span>قراءة {{ $post->reading_minutes }} دقائق</span>
                @endif
            </div>
        </div>
    </header>

    @if ($post->coverImage)
        <div class="mx-auto max-w-4xl px-5 pt-10 lg:px-8">
            <figure>
                {{-- Explicit dimensions reserve the space before the image
                     loads, so the article text does not jump. --}}
                <img src="{{ $post->coverImage->url() }}"
                     alt="{{ $post->coverImage->alt_ar }}"
                     width="{{ $post->coverImage->width }}"
                     height="{{ $post->coverImage->height }}"
                     class="w-full rounded-sm">
                @if ($post->coverImage->caption_ar)
                    <figcaption class="mt-3 text-sm text-charcoal-soft">{{ $post->coverImage->caption_ar }}</figcaption>
                @endif
            </figure>
        </div>
    @endif

    <div class="mx-auto max-w-3xl px-5 py-14 lg:px-8">
        <div class="prose-legal">
            {!! Content::public($post->body) !!}
        </div>

        <p class="mt-14 rounded-sm border border-stone bg-stone-soft/50 p-5 text-sm leading-relaxed text-charcoal-soft">
            هذا المقال ذو طابع تعريفي عام ولا يُعدّ استشارة قانونية في مسألة بعينها.
            تختلف المعالجة باختلاف وقائع كل حالة ومستنداتها، وقد تتغير الأنظمة والتعليمات
            بعد تاريخ النشر.
        </p>

        {{-- Related services: the commercially meaningful internal link on an
             article, connecting the reader's question to the office's work. --}}
        @if ($post->services->isNotEmpty())
            <section class="mt-14">
                <h2 class="font-display text-xl text-ink">خدمات ذات صلة</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach ($post->services as $service)
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

        @if ($post->relatedPosts->isNotEmpty())
            <section class="mt-12">
                <h2 class="font-display text-xl text-ink">اقرأ أيضاً</h2>
                <ul class="mt-5 space-y-3">
                    @foreach ($post->relatedPosts as $related)
                        <li>
                            <a href="{{ $related->href() }}"
                               class="group flex items-center gap-3 text-charcoal hover:text-gold-deep">
                                <span class="h-px w-4 bg-gold transition-all group-hover:w-6" aria-hidden="true"></span>
                                {{ $related->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</article>

<x-ui.cta-band
    title="لديك مسألة مشابهة؟"
    body="اعرض تفاصيل حالتك ومستنداتها على المكتب لتحديد المسار القانوني المناسب."
    placement="article_footer_cta" />

</x-layouts.public>
