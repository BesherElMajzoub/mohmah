@props([
    'title' => 'ناقش احتياجك القانوني مع المكتب',
    'body' => null,
    'placement' => 'cta_band',
    // Overridable because the homepage closes with shorter labels than the
    // inner pages, where the fuller wording carries more context.
    'callLabel' => 'اتصل بالمكتب',
    'whatsappLabel' => 'راسلنا عبر واتساب',
])

{{-- The closing conversion block.

     Wording is deliberately neutral and serious: no "استشارة مجانية", no
     urgency, no promise of an outcome. The reader is a company owner or an
     investor deciding whether to place a serious matter with this office —
     the invitation is to a conversation, not to a funnel. --}}

<section class="bg-ink text-ivory on-dark">
    <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-24">
        <div class="grid items-center gap-10 lg:grid-cols-[1fr_auto]">
            <div class="max-w-2xl">
                <span class="block h-px w-12 bg-gold" aria-hidden="true"></span>
                <h2 class="mt-6 font-display text-3xl leading-tight text-ivory md:text-4xl">
                    {{ $title }}
                </h2>
                <p class="mt-5 text-lg leading-relaxed text-ivory/75">
                    {{ $body ?? 'تواصل مع المكتب لعرض تفاصيل حالتك ومستنداتها، وتحديد المسار القانوني المناسب والخطوات العملية التالية.' }}
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row lg:flex-col xl:flex-row">
                <x-cta.call placement="{{ $placement }}" variant="on-dark" :label="$callLabel" />
                <x-cta.whatsapp placement="{{ $placement }}" variant="on-dark" :label="$whatsappLabel" />
            </div>
        </div>

        <p class="mt-10 text-sm text-ivory/50">
            <span class="text-gold-soft">هاتف المكتب:</span>
            <span class="num">{{ config('site.phone_display') }}</span>
        </p>
    </div>
</section>
