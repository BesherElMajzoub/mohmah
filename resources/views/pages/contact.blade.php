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
            <h1 class="font-display text-3xl leading-tight text-ink md:text-5xl">تواصل مع المكتب</h1>
            <p class="mt-6 text-lg leading-relaxed text-charcoal-soft">
                للاتصال المباشر أو مراسلة المكتب عبر واتساب لمناقشة احتياجك القانوني
                وتحديد الخطوة التالية.
            </p>
        </div>
    </div>
</section>

<div class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-20">
    <div class="grid gap-14 lg:grid-cols-2 lg:gap-20">

        {{-- Direct contact first, and always available. Nobody is made to
             complete a form before they can reach the office. --}}
        <div>
            <h2 class="font-display text-2xl text-ink">الاتصال المباشر</h2>

            <div class="mt-8 space-y-4">
                <a href="{{ config('site.tel_href') }}"
                   data-track="call"
                   data-placement="contact_page"
                   class="group flex items-center justify-between gap-4 rounded-sm border border-stone
                          bg-ivory p-6 transition-colors hover:border-gold">
                    <span>
                        <span class="block text-sm text-charcoal-soft">هاتف المكتب</span>
                        <span class="mt-1 block font-display text-2xl text-ink num">
                            {{ config('site.phone_display') }}
                        </span>
                    </span>
                    <span class="text-gold transition-transform group-hover:-translate-x-1" aria-hidden="true">←</span>
                </a>

                <a href="{{ config('site.whatsapp_href') }}"
                   target="_blank" rel="noopener"
                   data-track="whatsapp"
                   data-placement="contact_page"
                   class="group flex items-center justify-between gap-4 rounded-sm border border-stone
                          bg-ivory p-6 transition-colors hover:border-gold">
                    <span>
                        <span class="block text-sm text-charcoal-soft">واتساب</span>
                        <span class="mt-1 block font-display text-2xl text-ink num">
                            {{ config('site.phone_display') }}
                        </span>
                    </span>
                    <span class="text-gold transition-transform group-hover:-translate-x-1" aria-hidden="true">←</span>
                </a>

                {{-- Email, address, map and hours each render only once a real
                     value exists in settings. Nothing here is invented. --}}
                @if ($settings->filled('office_email'))
                    <a href="mailto:{{ $settings->get('office_email') }}"
                       class="group flex items-center justify-between gap-4 rounded-sm border border-stone
                              bg-ivory p-6 transition-colors hover:border-gold">
                        <span>
                            <span class="block text-sm text-charcoal-soft">البريد الإلكتروني</span>
                            <span class="mt-1 block font-display text-lg text-ink" dir="ltr">
                                {{ $settings->get('office_email') }}
                            </span>
                        </span>
                        <span class="text-gold" aria-hidden="true">←</span>
                    </a>
                @endif
            </div>

            {{-- Address, map and hours are not repeated here — the location
                 section below the fold carries them, and duplicating them
                 would put the same conditional block on the page twice. --}}
        </div>

        {{-- Optional form ------------------------------------------------- --}}
        <div>
            @if (session('status'))
                <div role="status"
                     class="mb-6 rounded-sm border border-gold bg-stone-soft/60 p-5 text-charcoal">
                    {{ session('status') }}
                </div>
            @endif

            @if ($formEnabled)
                <h2 class="font-display text-2xl text-ink">أرسل رسالة</h2>
                <p class="mt-3 text-sm text-charcoal-soft">
                    للاتصال العاجل يُفضّل الهاتف أو واتساب.
                </p>

                <form method="POST" action="{{ route('contact.store') }}" class="mt-8 space-y-5">
                    @csrf

                    {{-- Honeypot: hidden from people, tempting to bots. Kept
                         out of the tab order and hidden from screen readers
                         so it never traps a real visitor. --}}
                    <div class="hidden" aria-hidden="true">
                        <label for="website">لا تملأ هذا الحقل</label>
                        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                    </div>

                    <x-form.field name="name" label="الاسم" required />
                    <x-form.field name="phone" label="رقم الجوال" type="tel" required dir="ltr" />
                    <x-form.field name="email" label="البريد الإلكتروني (اختياري)" type="email" dir="ltr" />
                    <x-form.field name="subject" label="موضوع الرسالة (اختياري)" />
                    <x-form.field name="message" label="تفاصيل احتياجك القانوني" type="textarea" required rows="6" />

                    <button type="submit"
                            class="w-full rounded-sm bg-ink px-7 py-3.5 font-display text-ivory
                                   transition-colors hover:bg-ink-700 sm:w-auto">
                        إرسال الرسالة
                    </button>

                    <p class="text-xs leading-relaxed text-charcoal-soft">
                        بإرسال هذا النموذج تقرّ بأن إرساله لا ينشئ علاقة توكيل مع المكتب،
                        وأن تفاصيل حالتك تُعامَل بسرّية.
                        <a href="{{ route('privacy') }}" class="underline underline-offset-4">سياسة الخصوصية</a>
                    </p>
                </form>
            @else
                {{-- When the form is off, this space carries the reassurance
                     the form would otherwise carry, rather than sitting
                     empty. --}}
                <div class="rounded-sm bg-ink p-8 text-ivory on-dark">
                    <h2 class="font-display text-2xl text-ivory">قبل أن تتصل</h2>
                    <p class="mt-5 leading-relaxed text-ivory/75">
                        يساعد على تحديد المسار بسرعة أن تكون المستندات الأساسية في متناولك:
                        العقد أو السند محل المسألة، والمراسلات بين الأطراف، وأي إشعار أو قرار
                        صدر بشأنها.
                    </p>
                    <p class="mt-4 leading-relaxed text-ivory/75">
                        ما يصل إلى المكتب من معلومات يخضع لواجب السرّية المهنية.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <x-cta.call placement="contact_panel" variant="on-dark" label="اتصل الآن" />
                        <x-cta.whatsapp placement="contact_panel" variant="on-dark" label="واتساب" />
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<x-ui.location placement="contact_location" tone="dark" :show-contact="false" />

</x-layouts.public>
