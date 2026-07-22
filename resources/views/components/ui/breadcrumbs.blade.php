@props(['items' => []])

@if (count($items) > 1)
    {{-- The visible trail. BreadcrumbList JSON-LD is emitted separately by the
         page, from this same array — the markup and the structured data can
         never disagree because they read the same source. --}}
    <nav aria-label="مسار التصفح" {{ $attributes->class('text-sm') }}>
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
            @foreach ($items as $index => $item)
                <li class="flex items-center gap-2">
                    @if (! $loop->last && ! empty($item['path']))
                        <a href="{{ \App\Support\Url::encodePath($item['path']) }}"
                           class="text-charcoal-soft underline-offset-4 transition-colors hover:text-ink hover:underline">
                            {{ $item['title'] }}
                        </a>
                    @else
                        <span aria-current="page" class="text-charcoal">{{ $item['title'] }}</span>
                    @endif

                    @unless ($loop->last)
                        {{-- A directional chevron would point the wrong way in
                             RTL; a neutral slash reads correctly either way. --}}
                        <span class="text-gold/60" aria-hidden="true">/</span>
                    @endunless
                </li>
            @endforeach
        </ol>
    </nav>
@endif
