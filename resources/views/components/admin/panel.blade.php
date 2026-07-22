@props(['title', 'description' => null])

{{-- The one card shape used across every admin form, so the screens read as
     one system rather than a series of differently-shaped forms. --}}
<section {{ $attributes->class('rounded-sm border border-stone bg-ivory p-6') }}>
    <h2 class="font-display text-lg text-ink">{{ $title }}</h2>

    @if ($description)
        <p class="mt-2 text-sm leading-relaxed text-charcoal-soft">{{ $description }}</p>
    @endif

    <div class="mt-6 space-y-5">
        {{ $slot }}
    </div>
</section>
