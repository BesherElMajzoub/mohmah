<x-layouts.admin title="نظرة عامة">

{{-- Conversion counters ------------------------------------------------ --}}
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @php
        $stats = [
            ['label' => 'نقرات اليوم', 'value' => $clicksToday],
            ['label' => 'نقرات هذا الشهر', 'value' => $clicksMonth],
            ['label' => 'اتصالات هذا الشهر', 'value' => $callsMonth],
            ['label' => 'واتساب هذا الشهر', 'value' => $whatsappMonth],
        ];
    @endphp

    @foreach ($stats as $stat)
        <div class="rounded-sm border border-stone bg-ivory p-6">
            <p class="text-sm text-charcoal-soft">{{ $stat['label'] }}</p>
            <p class="mt-2 font-display text-3xl text-ink num">{{ number_format($stat['value']) }}</p>
        </div>
    @endforeach
</div>

<p class="mt-4 text-sm">
    <a href="{{ route('admin.clicks.index') }}" class="text-gold-deep underline-offset-4 hover:underline">
        تفاصيل تتبع النقرات ←
    </a>
</p>

<div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">

    {{-- Outstanding client inputs --------------------------------------
         This list is why unsupplied facts never turn into invented ones:
         the missing information stays visible here instead of quietly
         becoming a plausible placeholder on the public site. --}}
    <section class="rounded-sm border border-stone bg-ivory p-6">
        <h2 class="font-display text-lg text-ink">ما يحتاجه الموقع منك</h2>
        <p class="mt-2 text-sm leading-relaxed text-charcoal-soft">
            العناصر غير المكتملة لا تظهر في الموقع إطلاقاً — لا يُعرض أي عنوان أو
            رقم أو حساب تقريبي بدلاً منها.
        </p>

        <ul class="mt-6 divide-y divide-stone">
            @foreach ($outstanding as $item)
                <li class="flex items-start gap-4 py-4">
                    <span @class([
                        'mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full text-xs',
                        'bg-ink text-ivory' => $item['done'],
                        'border border-gold text-gold-deep' => ! $item['done'],
                    ]) aria-hidden="true">
                        {{ $item['done'] ? '✓' : '!' }}
                    </span>
                    <span class="min-w-0">
                        <span class="block font-display text-[0.95rem] {{ $item['done'] ? 'text-charcoal-soft line-through' : 'text-ink' }}">
                            {{ $item['label'] }}
                        </span>
                        <span class="mt-1 block text-sm leading-relaxed text-charcoal-soft">{{ $item['hint'] }}</span>
                    </span>
                    <span class="sr-only">{{ $item['done'] ? 'مكتمل' : 'غير مكتمل' }}</span>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Content counts + quick actions --------------------------------- --}}
    <div class="space-y-6">
        <section class="rounded-sm border border-stone bg-ivory p-6">
            <h2 class="font-display text-lg text-ink">المحتوى</h2>

            <dl class="mt-5 space-y-3 text-sm">
                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-charcoal-soft">خدمات منشورة</dt>
                    <dd class="font-display text-ink num">{{ $servicesPublished }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-charcoal-soft">خدمات مسودة</dt>
                    <dd class="font-display text-ink num">{{ $servicesDraft }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-t border-stone pt-3">
                    <dt class="text-charcoal-soft">مقالات منشورة</dt>
                    <dd class="font-display text-ink num">{{ $postsPublished }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-charcoal-soft">مقالات مسودة</dt>
                    <dd class="font-display text-ink num">{{ $postsDraft }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-sm border border-stone bg-ivory p-6">
            <h2 class="font-display text-lg text-ink">إجراءات سريعة</h2>
            <div class="mt-5 flex flex-col gap-2.5">
                <a href="{{ route('admin.posts.create') }}"
                   class="rounded-sm bg-ink px-5 py-3 text-center font-display text-sm text-ivory hover:bg-ink-700">
                    كتابة مقال جديد
                </a>
                <a href="{{ route('admin.services.create') }}"
                   class="rounded-sm border border-gold px-5 py-3 text-center font-display text-sm text-ink hover:bg-stone-soft">
                    إضافة خدمة
                </a>
                <a href="{{ route('admin.settings.edit') }}"
                   class="rounded-sm border border-stone px-5 py-3 text-center text-sm text-charcoal hover:bg-stone-soft">
                    إعدادات الموقع
                </a>
            </div>

            <p class="mt-5 rounded-sm bg-stone-soft/60 p-4 text-xs leading-relaxed text-charcoal-soft">
                عند نشر مقال جديد يُضاف تلقائياً إلى خريطة الموقع
                <span dir="ltr">sitemap.xml</span> فوراً، دون أي خطوة إضافية.
            </p>
        </section>

        @if ($unreadSubmissions > 0)
            <a href="{{ route('admin.submissions.index') }}"
               class="block rounded-sm border-s-4 border-gold bg-ivory p-5 text-sm hover:bg-stone-soft/40">
                <span class="font-display text-ink">
                    {{ $unreadSubmissions }} رسالة جديدة من نموذج التواصل
                </span>
            </a>
        @endif
    </div>
</div>

</x-layouts.admin>
