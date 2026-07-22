@props([
    'label' => 'اتصل الآن',
    'placement' => 'unknown',
    'variant' => 'primary',
])

@php
    // The variants a conversion button actually needs. Gold is a hairline
    // border on the dark variant, never a fill — a solid gold button is the
    // exact "black and gold legal template" look the identity rules out.
    $variants = [
        'primary' => 'bg-ink text-ivory hover:bg-ink-700 border border-transparent',
        'outline' => 'bg-transparent text-ink border border-gold hover:bg-ink hover:text-ivory',
        'on-dark' => 'bg-ivory text-ink border border-transparent hover:bg-stone-soft',
    ];
@endphp

{{-- A plain anchor with a real tel: href.

     Tracking is attached via data attributes and read by a delegated
     listener, never by an inline onclick and never by preventing the
     default. If JavaScript fails, is blocked, or the tracking endpoint is
     down, this is still a working phone link — the conversion path does not
     depend on analytics. --}}
<a href="{{ config('site.tel_href') }}"
   data-track="call"
   data-placement="{{ $placement }}"
   {{ $attributes->class([
       'inline-flex items-center justify-center gap-2.5 rounded-sm px-7 py-3.5',
       'font-display text-base transition-colors duration-200',
       $variants[$variant] ?? $variants['primary'],
   ]) }}>
    <svg class="size-[1.1em] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/>
    </svg>
    <span>{{ $label }}</span>
</a>
