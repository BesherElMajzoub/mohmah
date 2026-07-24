@props([
    'placement' => 'location',
    'tone' => 'light',
    // The contact page already leads with the phone and WhatsApp cards, so it
    // renders this section without repeating them.
    'showContact' => true,
])

@php
    $settings = app(\App\Support\Settings::class);

    $hasAddress = $settings->filled('office_address');
    $hasHours = $settings->filled('office_hours');

    $dark = $tone === 'dark';
@endphp

{{-- Where the office is.

     Only the city is asserted, because only the city has been supplied. The
     street address, map link and working hours each appear on their own the
     moment a real value is entered in settings — until then nothing stands in
     for them. An invented address on a law office page is a factual claim, and
     a client who drives to it is worse off than one who simply called. --}}

<section class="{{ $dark ? 'bg-ink text-ivory on-dark' : 'border-b border-stone bg-ivory-dim' }}">
    <div class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-20">
        <div class="grid gap-10 lg:gap-16 {{ $showContact ? 'lg:grid-cols-[1.15fr_1fr]' : '' }}">

            <div>
                <p class="flex items-center gap-3 text-sm {{ $dark ? 'text-gold-soft' : 'text-gold-deep' }}">
                    <span class="h-px w-8 bg-gold" aria-hidden="true"></span>
                    موقع المكتب
                </p>

                <h2 class="mt-5 font-display text-3xl leading-tight {{ $dark ? 'text-ivory' : 'text-ink' }} md:text-4xl">
                    {{ config('site.city') }} — {{ config('site.country') }}
                </h2>

                <p class="mt-6 max-w-xl text-lg leading-relaxed {{ $dark ? 'text-ivory/75' : 'text-charcoal-soft' }}">
                    مقرّ المكتب في مدينة {{ config('site.city') }}، ويعمل مع الشركات والمستثمرين
                    وملّاك العقارات في المسائل التجارية والتحكيمية والتوثيقية والعقارية.
                </p>

                @if ($hasAddress)
                    <address class="mt-7 not-italic leading-relaxed {{ $dark ? 'text-ivory/75' : 'text-charcoal' }}">
                        {{ $settings->get('office_address') }}
                    </address>
                @endif

                @if ($hasHours)
                    <p class="mt-5 {{ $dark ? 'text-ivory/75' : 'text-charcoal' }}">
                        <span class="{{ $dark ? 'text-gold-soft' : 'text-gold-deep' }}">أوقات العمل:</span>
                        {{ $settings->get('office_hours') }}
                    </p>
                @endif

                {{-- No map link here: the map panel below carries it, and the
                     office should not be linked to Google Maps twice in one
                     section. --}}
            </div>

            {{-- Direct contact. Kept in this section because "where are you"
                 and "how do I reach you" are the same question. --}}
            @if ($showContact)
            <div class="{{ $dark ? 'border-gold/30 bg-ink-700/40' : 'border-stone bg-ivory' }}
                        self-start rounded-sm border p-7 lg:p-8">
                <p class="font-display text-lg {{ $dark ? 'text-ivory' : 'text-ink' }}">
                    للتواصل المباشر
                </p>

                <p class="mt-2.5 text-sm {{ $dark ? 'text-ivory/65' : 'text-charcoal-soft' }}">
                    اتصال هاتفي أو رسالة عبر واتساب لمناقشة احتياجك القانوني.
                </p>

                <p class="mt-5">
                    <a href="{{ config('site.tel_href') }}"
                       data-track="call"
                       data-placement="{{ $placement }}"
                       class="num font-display text-2xl transition-colors
                              {{ $dark ? 'text-gold-soft hover:text-gold' : 'text-ink hover:text-gold-deep' }}">
                        {{ config('site.phone_display') }}
                    </a>
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <x-cta.call :placement="$placement"
                                :variant="$dark ? 'on-dark' : 'primary'"
                                label="اتصل الآن"
                                class="!px-5 !py-3 !text-sm" />
                    <x-cta.whatsapp :placement="$placement"
                                    :variant="$dark ? 'on-dark' : 'outline'"
                                    label="واتساب"
                                    class="!px-5 !py-3 !text-sm" />
                </div>
            </div>
            @endif
        </div>

        {{-- The map lives in the footer, where it is reachable from every page
             rather than only from the two that carry this section. --}}
    </div>
</section>
