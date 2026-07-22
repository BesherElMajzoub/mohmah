@props(['items' => []])

@if (! empty($items))
    {{-- Native <details> rather than a scripted accordion: it is keyboard
         accessible and expandable by find-in-page with no JavaScript, and the
         answers are in the DOM for crawlers whether opened or not.

         Note there is no FAQPage structured data on this component. FAQ rich
         results are the most commonly abused markup in legal SEO, and Google
         has restricted them to authoritative government and health sites — so
         adding it would be markup with no eligible benefit. The answers are
         here for readers. --}}
    <div {{ $attributes->class('divide-y divide-stone border-y border-stone') }}>
        @foreach ($items as $item)
            @continue(empty($item['question']) || empty($item['answer']))

            <details class="group">
                <summary class="flex cursor-pointer list-none items-start justify-between gap-4 py-6
                                font-display text-lg text-ink transition-colors hover:text-gold-deep">
                    <span>{{ $item['question'] }}</span>
                    <span class="mt-1 shrink-0 text-gold transition-transform duration-200 group-open:rotate-45"
                          aria-hidden="true">+</span>
                </summary>
                <div class="pb-6 leading-loose text-charcoal-soft">
                    {{ $item['answer'] }}
                </div>
            </details>
        @endforeach
    </div>
@endif
