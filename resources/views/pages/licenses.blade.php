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

    {{-- Each licence as its own card, with the scope it covers stated in
         plain terms. Nothing is added beyond the number and the label — no
         issuing authority, no dates, no verification badge, because none
         were supplied and a law office cannot imply a credential it has not
         evidenced. --}}
    @php
        $scopes = [
            'advocacy' => 'يتيح تمثيل العملاء والترافع أمام المحاكم والجهات القضائية وشبه القضائية، وإعداد المذكرات واللوائح.',
            'notarization' => 'يتيح توثيق الوكالات وعقود الشركات والإقرارات والتصرفات، وما يتصل بها من محررات.',
            'arbitration' => 'يتيح العمل محكّماً في المنازعات التجارية، إلى جانب تمثيل الأطراف وإدارة إجراءات التحكيم.',
            'real_estate' => 'يتيح مباشرة إجراءات التسجيل العيني للعقار وإعداد ملفاته ومتابعة الاعتراضات عليه.',
        ];
    @endphp

    <div class="grid gap-6 md:grid-cols-2">
        @foreach (config('site.licenses') as $license)
            <div class="rounded-sm border border-stone bg-ivory p-8">
                <span class="block h-px w-10 bg-gold" aria-hidden="true"></span>

                <p class="mt-5 text-sm text-charcoal-soft">{{ $license['label'] }}</p>

                {{-- dir="ltr" prevents the bidi algorithm from reordering the
                     Latin letters, digits and hyphens inside an RTL block —
                     the arbitration number in particular. --}}
                <p class="mt-2 font-display text-3xl text-ink" dir="ltr" style="text-align: start;">
                    {{ $license['number'] }}
                </p>

                @if (isset($scopes[$license['key']]))
                    <p class="mt-5 leading-relaxed text-charcoal-soft">{{ $scopes[$license['key']] }}</p>
                @endif
            </div>
        @endforeach
    </div>

    @if (filled($page->body))
        <div class="prose-legal mt-14 max-w-3xl">
            {!! Content::public($page->body) !!}
        </div>
    @endif

    <p class="mt-12 max-w-3xl rounded-sm border border-stone bg-stone-soft/50 p-5 text-sm leading-relaxed text-charcoal-soft">
        تُعرض أرقام التراخيص أعلاه كما وردت. وللتحقق من أي ترخيص مهني، يُرجع إلى الجهة
        المختصة بإصداره.
    </p>
</div>

<x-ui.cta-band placement="licenses_footer_cta" />

</x-layouts.public>
