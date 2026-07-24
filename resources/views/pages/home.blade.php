<x-layouts.public :seo="$seo">

@push('schema')
    {{-- The homepage adds nothing beyond the organisation and website nodes
         already emitted by the layout; breadcrumbs on a root page would be a
         single-item trail. --}}
@endpush

{{-- ===================================================================
     Hero
     ===================================================================
     Type-led rather than image-led. The office has supplied no photography,
     and a stock courtroom or gavel image is precisely the generic legal look
     the identity rules out — so the hero is built from the wordmark, the
     document-grid motif and gold linework. It is also very fast: no hero
     image means nothing large competes with LCP.
     =================================================================== --}}
{{-- The page opens on the weight of the decision, not on a service list and
     not on a scales-of-justice image. Everything above the fold argues one
     thing: matters of this size deserve representation of matching seriousness.

     Type-led by necessity as well as by choice — no photography has been
     supplied, and stock legal imagery is exactly the generic look the identity
     rules out. It is also the fastest possible hero: nothing large competes
     with LCP. --}}
<section class="relative isolate overflow-hidden bg-ink text-ivory on-dark">
    <div class="grid-motif absolute inset-0 opacity-60" aria-hidden="true"></div>

    {{-- A soft ink gradient keeps the grid from reading as a hard texture
         behind the headline. --}}
    <div class="absolute inset-0 bg-gradient-to-bl from-ink-950/0 via-ink/70 to-ink" aria-hidden="true"></div>

    <div class="relative mx-auto grid max-w-7xl items-end gap-y-0 px-5 lg:grid-cols-12 lg:gap-x-8 lg:px-8">

        {{-- --- The argument ------------------------------------------- --}}
        <div class="pb-16 pt-24 lg:col-span-7 lg:self-center lg:pb-28 lg:pt-28">
            <p class="rise flex items-center gap-3 text-sm text-gold-soft" style="--i: 0">
                <span class="h-px w-10 bg-gold" aria-hidden="true"></span>
                مكتب المحامي ريان الجهني — {{ config('site.city') }}
            </p>

            {{-- Arabic needs the looser leading; at this size the default
                 would collide the descenders against the following line. --}}
            <h1 class="rise mt-8 font-display text-[2rem] leading-[1.4] text-ivory
                       sm:text-4xl sm:leading-[1.35] lg:text-[2.875rem] lg:leading-[1.3]"
                style="--i: 1">
                عندما تكون قيمة القرار كبيرة، تحتاج إلى تمثيل قانوني يوازيها
            </h1>

            {{-- A gold hairline between the claim and its explanation — the
                 identity's structural device, doing real work here. --}}
            <span class="rise mt-9 block h-px w-24 bg-gold/60" style="--i: 2" aria-hidden="true"></span>

            <p class="rise mt-8 max-w-xl text-lg leading-relaxed text-ivory/80" style="--i: 3">
                يقدم مكتب المحامي ريان الجهني في {{ config('site.city') }} خدمات المحاماة والتحكيم
                والتوثيق والخدمات العقارية للأعمال والمعاملات التي تتطلب دقة، خصوصية،
                ومساراً قانونياً واضحاً.
            </p>

            <p class="rise mt-5 max-w-xl leading-relaxed text-ivory/65" style="--i: 4">
                من العقود والمنازعات التجارية، إلى التحكيم والتوثيق والتسجيل العيني للعقار —
                نساعدك على اتخاذ الخطوة القانونية الصحيحة قبل أن تتحول المسألة إلى خسارة أكبر.
            </p>

            <div class="rise mt-10 flex flex-col gap-3 sm:flex-row" style="--i: 5">
                <x-cta.call placement="hero" variant="on-dark" label="اتصل بالمكتب" />
                <x-cta.whatsapp placement="hero" variant="on-dark" label="راسلنا عبر واتساب" />
            </div>

            <p class="rise mt-6 text-sm text-ivory/45" style="--i: 6">
                <span class="num">{{ config('site.phone_display') }}</span>
            </p>
        </div>

        {{-- --- The lawyer --------------------------------------------------
             Bottom-aligned with no padding beneath, so the figure stands on
             the section edge rather than floating in it. --}}
        <div class="relative lg:col-span-5 lg:self-end">
            <div class="relative mx-auto w-full max-w-[19rem] lg:max-w-none">

                {{-- The arch sits behind and slightly inside the figure's
                     footprint, so his shoulders break its outline instead of
                     being contained by it. --}}
                <div class="portrait-arch absolute inset-x-4 bottom-0 top-10 lg:inset-x-8 lg:top-16"
                     aria-hidden="true"></div>

                <img src="{{ asset('brand/lawyer-portrait-750.webp') }}"
                     srcset="{{ asset('brand/lawyer-portrait-500.webp') }} 500w,
                             {{ asset('brand/lawyer-portrait-750.webp') }} 750w,
                             {{ asset('brand/lawyer-portrait-1003.webp') }} 1003w"
                     sizes="(min-width: 1024px) 40vw, 19rem"
                     alt="المحامي ريان الجهني"
                     width="1003"
                     height="1568"
                     fetchpriority="high"
                     decoding="async"
                     class="relative block w-full">
            </div>
        </div>
    </div>
</section>

{{-- ===================================================================
     Qualification
     ===================================================================
     Placed second on purpose. A visitor who recognises their own situation
     in this list has self-qualified before reading a single service page,
     and one who does not belongs somewhere else — which is the point, given
     the office does not take general personal matters.
     =================================================================== --}}
<section class="border-b border-stone bg-ivory">
    <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-24">
        <x-ui.section-heading eyebrow="قبل أن تتواصل" level="h2">
            هل يناسبك هذا المكتب؟
        </x-ui.section-heading>

        @php
            // Written as situations the reader recognises, not as services.
            $qualifiers = [
                'لديك شركة أو عقد تجاري يحتاج إلى صياغة أو مراجعة دقيقة.',
                'تواجه نزاعاً تجارياً أو مطالبة مالية ذات قيمة مرتفعة.',
                'تحتاج إلى تمثيل أو إدارة إجراءات تحكيم تجاري.',
                'لديك معاملة عقارية أو تسجيل عيني أو نقل ملكية يحتاج إلى متابعة نظامية.',
                'تحتاج إلى توثيق وكالة أو عقد شركة أو إجراءات مرتبطة بالورثة والعقار.',
            ];
        @endphp

        <ul class="mt-12 grid gap-x-12 gap-y-1 md:grid-cols-2">
            @foreach ($qualifiers as $qualifier)
                <li class="flex items-start gap-4 border-b border-stone py-5">
                    {{-- A gold rule rather than a tick: the identity's linework,
                         and it avoids the checklist-of-promises look. --}}
                    <span class="mt-[0.95em] h-px w-5 shrink-0 bg-gold" aria-hidden="true"></span>
                    <span class="text-[1.0625rem] leading-relaxed text-charcoal">{{ $qualifier }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- ===================================================================
     Licence trust — deliberately brief
     ===================================================================
     One line, one link. The four numbers themselves live on the licences
     page and further down this page; repeating them here would turn a
     reassurance into a wall.
     =================================================================== --}}
<section class="bg-ivory-dim">
    <div class="mx-auto max-w-7xl px-5 py-14 lg:px-8">
        <div class="flex flex-col items-start gap-6 border-s-2 border-gold ps-8 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="font-display text-xl text-ink">تراخيص مهنية متعددة لخدمة ملفك</p>
                <p class="mt-2.5 text-[0.95rem] text-charcoal-soft">
                    محاماة مرخصة <span class="text-gold" aria-hidden="true">·</span>
                    توثيق مرخص <span class="text-gold" aria-hidden="true">·</span>
                    تحكيم مرخص <span class="text-gold" aria-hidden="true">·</span>
                    تسجيل عيني للعقار مرخص
                </p>
            </div>

            <a href="{{ route('licenses') }}"
               class="group inline-flex shrink-0 items-center gap-2.5 font-display text-ink transition-colors hover:text-gold-deep">
                اطلع على التراخيص والاعتمادات
                <span class="h-px w-5 bg-gold transition-all group-hover:w-8" aria-hidden="true"></span>
            </a>
        </div>
    </div>
</section>

{{-- ===================================================================
     The position
     ===================================================================
     The most important section on the page. Refusing to promise an outcome
     is both the honest position and the persuasive one for a client with
     something substantial at stake — and it is the opposite of how most
     legal sites read. Given weight accordingly: dark, wide, unhurried.
     =================================================================== --}}
<section class="bg-ink text-ivory on-dark">
    <div class="relative isolate overflow-hidden">
        <div class="grid-motif absolute inset-0 opacity-40" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-4xl px-5 py-24 lg:px-8 lg:py-32">
            <h2 class="font-display text-3xl leading-[1.4] text-ivory md:text-[2.5rem] md:leading-[1.35]">
                لا نبيع وعوداً، بل نبني مساراً قانونياً واضحاً
            </h2>

            <span class="mt-9 block h-px w-24 bg-gold/60" aria-hidden="true"></span>

            <p class="mt-9 text-lg leading-[2] text-ivory/75 md:text-xl md:leading-[1.95]">
                لا يمكن لأي محامٍ أن يضمن نتيجة قضية أو معاملة، لكن يمكنه أن يبدأ بدراسة
                دقيقة، ويحدد المخاطر والخيارات، ثم يتابع الملف بالتمثيل أو التوثيق أو
                التحكيم المناسب.
            </p>
        </div>
    </div>
</section>

{{-- ===================================================================
     Four pillars
     =================================================================== --}}
<section class="bg-ivory">
    <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
        <x-ui.section-heading eyebrow="مجالات العمل" level="h2">
            أربعة مجالات رئيسية
            <x-slot:description>
                يركّز المكتب على المسائل ذات الأثر التجاري والمالي المباشر، ويعمل مع
                الشركات والمستثمرين وملّاك العقارات والورثة في القضايا التي تتطلب دقة
                في الصياغة والإجراء.
            </x-slot:description>
        </x-ui.section-heading>

        <div class="mt-14 grid gap-px overflow-hidden rounded-sm bg-stone md:grid-cols-2">
            @foreach ($pillars as $pillar)
                <div class="bg-ivory p-8 lg:p-10">
                    <p class="font-display text-2xl text-ink">{{ $pillar->title }}</p>

                    @if ($pillar->intro)
                        <p class="mt-4 leading-relaxed text-charcoal-soft">{{ $pillar->intro }}</p>
                    @endif

                    @if ($pillar->services->isNotEmpty())
                        <ul class="mt-6 space-y-2.5">
                            @foreach ($pillar->services as $service)
                                <li>
                                    <a href="{{ $service->href() }}"
                                       class="group flex items-center gap-3 text-[0.95rem] text-charcoal
                                              transition-colors hover:text-gold-deep">
                                        <span class="h-px w-4 bg-gold transition-all group-hover:w-6"
                                              aria-hidden="true"></span>
                                        {{ $service->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>

        <p class="mt-10">
            <a href="{{ route('services.index') }}"
               class="inline-flex items-center gap-2 font-display text-ink underline-offset-4 hover:underline">
                جميع الخدمات
                <span aria-hidden="true">←</span>
            </a>
        </p>
    </div>
</section>

{{-- ===================================================================
     Methodology
     =================================================================== --}}
<section class="bg-ink text-ivory on-dark">
    <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
        <x-ui.section-heading eyebrow="منهجية العمل" level="h2" tone="dark">
            مسار عمل واضح من أول اتصال
            <x-slot:description>
                لا يبدأ العمل برأي سريع، بل بفهم دقيق للوقائع والمستندات. المراحل التالية
                تصف كيف يتعامل المكتب مع أي ملف.
            </x-slot:description>
        </x-ui.section-heading>

        @php
            // Process description, not a claim about outcomes or timelines.
            $steps = [
                ['title' => 'الاستماع وتحديد المسألة', 'body' => 'مناقشة أولية لفهم طبيعة النزاع أو المعاملة، والأطراف ذات العلاقة، والنتيجة التي يسعى إليها العميل.'],
                ['title' => 'مراجعة المستندات', 'body' => 'فحص العقود والمراسلات والسجلات والقيود ذات الصلة، وتحديد ما ينقص منها قبل اتخاذ أي موقف قانوني.'],
                ['title' => 'تحديد المسار وبدائله', 'body' => 'عرض الخيارات المتاحة — تسوية، تحكيم، أو ترافع — مع ما يترتب على كل مسار من إجراءات ومدد ومتطلبات إثبات.'],
                ['title' => 'التنفيذ والمتابعة', 'body' => 'إعداد المستندات والمرافعات وتمثيل العميل أمام الجهة المختصة، مع إطلاعه على مستجدات الملف أولاً بأول.'],
            ];
        @endphp

        <ol class="mt-14 grid gap-px overflow-hidden rounded-sm bg-ink-600 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($steps as $index => $step)
                <li class="bg-ink p-8">
                    <span class="font-display text-3xl text-gold num">{{ $index + 1 }}</span>
                    <p class="mt-4 font-display text-lg text-ivory">{{ $step['title'] }}</p>
                    <p class="mt-3 text-sm leading-relaxed text-ivory/70">{{ $step['body'] }}</p>
                </li>
            @endforeach
        </ol>

        <p class="mt-10">
            <a href="{{ route('methodology') }}"
               class="inline-flex items-center gap-2 font-display text-gold-soft underline-offset-4 hover:underline">
                تفاصيل منهجية العمل
                <span aria-hidden="true">←</span>
            </a>
        </p>
    </div>
</section>

{{-- ===================================================================
     Selected services
     =================================================================== --}}
@if ($featuredServices->isNotEmpty())
    <section class="bg-ivory-dim">
        <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
            <x-ui.section-heading eyebrow="خدمات مختارة" level="h2">
                خدمات يطلبها أصحاب الأعمال
            </x-ui.section-heading>

            <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($featuredServices as $service)
                    <x-ui.service-card :service="$service" />
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ===================================================================
     About the lawyer
     ===================================================================
     Deliberately short and free of any claim that has not been supplied: no
     years of experience, no education, no memberships, no case history. The
     licences are the verifiable substance; the profile page carries the rest
     once the client approves a biography.
     =================================================================== --}}
<section class="bg-ivory">
    <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
        <div class="grid items-center gap-14 lg:grid-cols-2">
            {{-- The approved portrait. Cropped to the upper body here rather
                 than repeating the full-length hero framing, so the two
                 appearances on one page read as different photographs. --}}
            <div class="relative aspect-[4/5] max-w-md overflow-hidden rounded-sm bg-ink lg:order-last">
                <div class="grid-motif absolute inset-0 opacity-50" aria-hidden="true"></div>

                {{-- object-cover with a top anchor. The source is far taller
                     than this 4:5 frame, so letting it sit at full width and
                     bottom-align cropped his head off — the crop has to come
                     off the robe, not the face. --}}
                <img src="{{ asset('brand/lawyer-portrait-750.webp') }}"
                     srcset="{{ asset('brand/lawyer-portrait-500.webp') }} 500w,
                             {{ asset('brand/lawyer-portrait-750.webp') }} 750w"
                     sizes="(min-width: 1024px) 28rem, 100vw"
                     alt="المحامي ريان الجهني"
                     width="750"
                     height="1172"
                     loading="lazy"
                     decoding="async"
                     class="absolute inset-0 h-full w-full object-cover object-top">

                <div class="pointer-events-none absolute inset-6 border border-gold/30" aria-hidden="true"></div>
            </div>

            <div>
                <x-ui.section-heading eyebrow="عن المحامي" level="h2">
                    ريان الجهني
                    <x-slot:description>
                        محامٍ مرخّص، ومحكّم، وموثّق، يعمل من {{ config('site.city') }} مع
                        الشركات والمستثمرين وملّاك العقارات في المسائل التجارية والتحكيمية
                        والتوثيقية والعقارية.
                    </x-slot:description>
                </x-ui.section-heading>

                <ul class="mt-8 space-y-3">
                    @foreach (config('site.licenses') as $license)
                        <li class="flex flex-wrap items-baseline gap-x-3 gap-y-1 border-b border-stone pb-3">
                            <span class="text-sm text-charcoal-soft">{{ $license['label'] }}</span>
                            <span class="font-display text-ink" dir="ltr">{{ $license['number'] }}</span>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-8">
                    <a href="{{ route('about') }}"
                       class="inline-flex items-center gap-2 font-display text-ink underline-offset-4 hover:underline">
                        الملف التعريفي للمحامي
                        <span aria-hidden="true">←</span>
                    </a>
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ===================================================================
     Latest articles — rendered only when there are approved articles.
     No placeholder posts, no "coming soon" grid.
     =================================================================== --}}
@if ($latestPosts->isNotEmpty())
    <section class="border-t border-stone bg-ivory-dim">
        <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-24">
            <x-ui.section-heading eyebrow="المدونة" level="h2">
                أحدث المقالات
            </x-ui.section-heading>

            <div class="mt-12 grid gap-8 md:grid-cols-3">
                @foreach ($latestPosts as $post)
                    <article>
                        <p class="text-sm text-gold-deep">{{ $post->category->title }}</p>
                        <h3 class="mt-2 font-display text-xl leading-snug">
                            <a href="{{ $post->href() }}" class="text-ink hover:text-gold-deep">
                                {{ $post->title }}
                            </a>
                        </h3>
                        @if ($post->excerpt)
                            <p class="mt-3 text-[0.95rem] leading-relaxed text-charcoal-soft">
                                {{ Str::limit($post->excerpt, 130) }}
                            </p>
                        @endif
                        <p class="mt-4 text-xs text-charcoal-soft">
                            <time datetime="{{ $post->published_at?->toDateString() }}">
                                {{ $post->published_at?->translatedFormat('j F Y') }}
                            </time>
                            @if ($post->reading_minutes)
                                — قراءة {{ $post->reading_minutes }} دقائق
                            @endif
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ===================================================================
     Search-intent guide
     ===================================================================
     One section, written once, that answers the phrasings people actually
     type — "محامي شركات"، "محامي توثيق عقود"، "محامي منازعات عقارية" — in
     honest prose instead of a doorway page per keyword. Each block states
     only work the office is licensed for; the closing paragraph converts
     the "افضل محامي" query into verifiable criteria rather than a boast.
     =================================================================== --}}
<section class="border-t border-stone bg-ivory">
    <div class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-24">
        <x-ui.section-heading eyebrow="عن ماذا تبحث؟" level="h2">
            مكتب محاماة في جدة يجمع تراخيص المحاماة والتوثيق والتحكيم والعقار
            <x-slot:description>
                من يبحث عن شركة محاماة أو مكتب محامي سعودي يحتاج قبل أي شيء إلى
                التحقق من الترخيص ضمن أسماء المحامين المعتمدين بوزارة العدل.
                مكتب المحامي ريان الجهني مرخّص في المحاماة برقم
                <span class="num" dir="ltr">432200</span>، ويعمل من جدة مع
                الشركات والمستثمرين وملّاك العقارات.
            </x-slot:description>
        </x-ui.section-heading>

        @php
            // Each entry answers a real search intent with work the office is
            // actually licensed to do — nothing here is a new service claim.
            $intents = [
                [
                    'title' => 'محامي شركات وتأسيس الشركات',
                    'body' => 'من صياغة عقد التأسيس إلى الحوكمة اليومية، يعمل المكتب بصفته محامي شركات: تأسيس الشركات، صياغة ومراجعة عقود الشركات والشركاء، وإجراءات تصفية الشركات عند إنهاء النشاط أو إعادة هيكلته.',
                ],
                [
                    'title' => 'محامي قضايا عقارية ومنازعات عقارية',
                    'body' => 'نزاعات الملكية والصكوك، والمطالبات الإيجارية، والتسجيل العيني للعقار — يتولى المكتب المنازعات العقارية بترافع مبني على المستندات والقيود النظامية، لا على الاجتهاد الشفهي.',
                ],
                [
                    'title' => 'محامٍ وموثّق — توثيق العقود والوكالات',
                    'body' => 'بصفته محامي توثيق مرخّصاً من وزارة العدل، يوثّق المكتب الوكالات وعقود الشركات والإقرارات، إضافة إلى المسائل المرتبطة بالورثة وقسمة التركات والقضايا الأسرية المتصلة بها.',
                ],
                [
                    'title' => 'منازعات شركات التأمين',
                    'body' => 'يمثّل المكتب المنشآت والأفراد في المطالبات ضد شركات التأمين أمام اللجان المختصة — من رفض التعويض إلى الخلاف على تقدير قيمته — بملف مستندي مكتمل قبل رفع الدعوى.',
                ],
                [
                    'title' => 'القضايا العمالية',
                    'body' => 'بصفته محامي قضايا عمالية، يعمل المكتب مع أصحاب الأعمال والعمال في منازعات عقود العمل والأجور ومستحقات نهاية الخدمة أمام المحاكم العمالية.',
                ],
                [
                    'title' => 'استشارة قانونية ورقم محامٍ في جدة',
                    'body' => 'سواء كنت تبحث عن شركة استشارات قانونية أو عن رقم محامٍ تتصل به مباشرة، يمكنك التواصل على ' . config('site.phone_display') . ' لعرض ملفك وتحديد المسار القانوني المناسب له.',
                ],
            ];
        @endphp

        {{-- Editorial rows, not a card grid: a block of equal boxes this
             close to the page end read as a second footer. The rows reuse
             the qualification section's device — gold dash, hairline rule —
             so the section reads as content, not as navigation. --}}
        <div class="mt-10 border-t border-stone">
            @foreach ($intents as $intent)
                <div class="grid gap-3 border-b border-stone py-8 lg:grid-cols-12 lg:gap-10">
                    <h3 class="flex items-start gap-4 font-display text-xl leading-snug text-ink lg:col-span-4">
                        <span class="mt-[0.8em] h-px w-5 shrink-0 bg-gold" aria-hidden="true"></span>
                        {{ $intent['title'] }}
                    </h3>
                    <p class="leading-relaxed text-charcoal-soft lg:col-span-8 lg:ps-0 ps-9">
                        {{ $intent['body'] }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- The "افضل محامي" intent, answered without self-praise: the claim
             is redirected to criteria the reader can verify. Set off with the
             identity's gold side-rule so it closes the section as a statement
             rather than trailing as fine print. --}}
        <div class="mt-12 border-s-2 border-gold ps-8">
            <p class="max-w-3xl leading-relaxed text-charcoal">
                معيار اختيار افضل محامي شركات أو أفضل مكتب محاماة ليس شعاراً يرفعه
                المكتب عن نفسه؛ المحامي الشاطر — كما يسميه الناس — هو من يملك ترخيصاً
                يمكن التحقق منه، ومنهجية عمل واضحة، وخبرة في ملفات مشابهة لملفك. هذه
                المعايير الثلاثة هي ما يعرضه هذا الموقع صفحةً صفحة.
            </p>
        </div>
    </div>
</section>

<x-ui.location placement="home_location" />

<x-ui.cta-band placement="home_footer_cta"
               title="كل ملف مهم يبدأ بخطوة واضحة"
               body="تواصل مع مكتب المحامي ريان الجهني لمناقشة احتياجك القانوني عبر الاتصال أو واتساب."
               call-label="اتصل الآن"
               whatsapp-label="واتساب" />

</x-layouts.public>
