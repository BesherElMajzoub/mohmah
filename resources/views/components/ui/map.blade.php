@props([
    'tone' => 'dark',
    'height' => 'h-52 lg:h-64',
])

@php
    $settings = app(\App\Support\Settings::class);

    $hasAddress = $settings->filled('office_address');
    $hasMap = $settings->filled('map_url');

    $dark = $tone === 'dark';

    // The map centres on the office address once one exists, and on the city
    // until then. It never drops a pin on a guessed location — a marker that
    // looks precise but is not is worse than no map at all.
    $query = $hasAddress
        ? $settings->get('office_address').'، '.config('site.city')
        : config('site.city').'، '.config('site.country');

    $embed = 'https://www.google.com/maps?q='.rawurlencode($query)
        .'&z='.($hasAddress ? 15 : 11).'&hl=ar&output=embed';

    $out = $hasMap
        ? $settings->get('map_url')
        : 'https://www.google.com/maps?q='.rawurlencode($query);
@endphp

{{-- The map, behind a click.

     Google Maps sets cookies and pulls a substantial script bundle the moment
     its iframe exists, so nothing loads until the visitor asks for it. Until
     then this is a styled panel with no third-party contact at all — which
     matters more here than anywhere else, because the footer is on every page
     of the site. A live embed here would mean every single page paid for it. --}}

<div x-data="{ loaded: true }" {{ $attributes }}>
    <div class="relative {{ $height }} overflow-hidden rounded-sm border
                {{ $dark ? 'border-gold/25 bg-ink-700/30' : 'border-stone bg-stone-soft' }}">

        <template x-if="loaded">
            <iframe src="{{ $embed }}"
                    title="خريطة موقع المكتب — {{ $query }}"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen
                    class="absolute inset-0 h-full w-full border-0"></iframe>
        </template>

        {{-- Facade --}}
        <div x-show="! loaded" class="absolute inset-0">
            <div class="grid-motif absolute inset-0 {{ $dark ? 'opacity-40' : 'opacity-25' }}" aria-hidden="true"></div>

            <div class="absolute inset-0 flex flex-col items-center justify-center gap-4 p-5 text-center">
                <p class="font-display {{ $dark ? 'text-ivory' : 'text-ink' }}">{{ $query }}</p>

                <button type="button"
                        @click="loaded = true"
                        class="rounded-sm border px-5 py-2.5 font-display text-sm transition-colors
                               {{ $dark
                                   ? 'border-gold text-ivory hover:bg-gold hover:text-ink'
                                   : 'border-gold text-ink hover:border-ink hover:bg-ink hover:text-ivory' }}">
                    عرض الخريطة
                </button>

                <p class="max-w-xs text-xs leading-relaxed {{ $dark ? 'text-ivory/40' : 'text-charcoal-soft' }}">
                    تُحمَّل الخريطة من خرائط جوجل، وقد تضع ملفات تعريف ارتباط خاصة بها.
                </p>
            </div>
        </div>
    </div>

    {{-- Without JavaScript the facade button does nothing, so a plain link out
         to Google Maps is always present as the fallback. --}}
    <p class="mt-3 text-xs {{ $dark ? 'text-ivory/45' : 'text-charcoal-soft' }}">
        <a href="{{ $out }}"
           target="_blank"
           rel="noopener"
           class="underline underline-offset-4 {{ $dark ? 'text-gold-soft' : 'text-gold-deep' }}">
            فتح الموقع في خرائط جوجل
        </a>
    </p>
</div>
