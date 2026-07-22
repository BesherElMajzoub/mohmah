@props(['service'])

{{-- The whole card is one link. A nested "read more" anchor would give
     screen-reader users two targets for one destination and shrink the touch
     area on mobile; the arrow is decorative and inherits the card's hover. --}}
<a href="{{ $service->href() }}"
   {{ $attributes->class([
       'group flex h-full flex-col rounded-sm border border-stone bg-ivory p-7',
       'transition-colors duration-200 hover:border-gold',
   ]) }}>
    <h3 class="font-display text-xl text-ink transition-colors group-hover:text-gold-deep">
        {{ $service->title }}
    </h3>

    @if ($service->summary)
        <p class="mt-3 flex-1 text-[0.95rem] leading-relaxed text-charcoal-soft">
            {{ $service->summary }}
        </p>
    @endif

    <span class="mt-6 flex items-center gap-2 text-sm text-gold-deep" aria-hidden="true">
        تفاصيل الخدمة
        <span class="transition-transform duration-200 group-hover:-translate-x-1">←</span>
    </span>
</a>
