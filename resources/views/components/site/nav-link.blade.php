@props(['href', 'active' => false])

{{-- aria-current tells a screen reader which page it is on; the gold
     underline says the same thing visually. Colour alone is never the only
     signal. --}}
<a href="{{ $href }}"
   @if ($active) aria-current="page" @endif
   {{ $attributes->class([
       'group/nav relative px-3.5 py-2.5 font-display text-[0.95rem] transition-colors',
       'text-gold-soft' => $active,
       'text-ivory/85 hover:text-gold-soft' => ! $active,
   ]) }}>
    {{ $slot }}

    {{-- A drawn hairline rather than text-decoration, so the underline can
         animate on hover and sits clear of the Arabic descenders instead of
         cutting through them. --}}
    <span class="absolute inset-x-3.5 bottom-1 h-px origin-center bg-gold transition-transform duration-300 ease-calm
                 {{ $active ? 'scale-x-100' : 'scale-x-0 group-hover/nav:scale-x-100' }}"
          aria-hidden="true"></span>
</a>
