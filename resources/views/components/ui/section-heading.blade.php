@props([
    'eyebrow' => null,
    'level' => 'h2',
    'align' => 'start',
    'tone' => 'light',
])

<div {{ $attributes->class([
    'max-w-3xl',
    'mx-auto text-center' => $align === 'center',
]) }}>
    @if ($eyebrow)
        {{-- A short gold rule plus a label. The rule is the identity's
             recurring linework motif, used here instead of an icon. --}}
        <p class="flex items-center gap-3 text-sm tracking-wide {{ $align === 'center' ? 'justify-center' : '' }}
                  {{ $tone === 'dark' ? 'text-gold-soft' : 'text-gold-deep' }}">
            <span class="h-px w-8 bg-gold" aria-hidden="true"></span>
            {{ $eyebrow }}
        </p>
    @endif

    <{{ $level }} class="mt-4 font-display text-3xl leading-tight md:text-4xl
                         {{ $tone === 'dark' ? 'text-ivory' : 'text-ink' }}">
        {{ $slot }}
    </{{ $level }}>

    @isset($description)
        <p class="mt-5 text-lg {{ $tone === 'dark' ? 'text-ivory/75' : 'text-charcoal-soft' }}">
            {{ $description }}
        </p>
    @endisset
</div>
