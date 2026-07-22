@props(['tone' => 'light', 'showLink' => true])

{{-- The four verified licences.

     Numbers are printed exactly as supplied — including the Arabic-Indic
     digits in the notarisation number and the spacing in the arbitration
     number. No issuing authority, no issue or expiry date, no verification
     link and no "verified" badge appears here, because none of those were
     supplied and a law office cannot imply a credential it has not
     evidenced. --}}

<div {{ $attributes }}>
    <ul class="grid gap-px overflow-hidden rounded-sm sm:grid-cols-2 lg:grid-cols-4
               {{ $tone === 'dark' ? 'bg-ink-600' : 'bg-stone' }}">
        @foreach (config('site.licenses') as $license)
            <li class="p-6 {{ $tone === 'dark' ? 'bg-ink' : 'bg-ivory' }}">
                <p class="text-sm {{ $tone === 'dark' ? 'text-ivory/60' : 'text-charcoal-soft' }}">
                    {{ $license['label'] }}
                </p>
                {{-- dir="ltr" on the number: these identifiers contain Latin
                     letters, digits and hyphens, which the bidi algorithm
                     would otherwise reorder inside an RTL paragraph — the
                     arbitration number in particular. --}}
                <p class="mt-2 font-display text-xl {{ $tone === 'dark' ? 'text-gold-soft' : 'text-ink' }}"
                   dir="ltr" style="text-align: start;">
                    {{ $license['number'] }}
                </p>
            </li>
        @endforeach
    </ul>

    @if ($showLink)
        <p class="mt-6 text-sm">
            <a href="{{ route('licenses') }}"
               class="inline-flex items-center gap-2 underline-offset-4 hover:underline
                      {{ $tone === 'dark' ? 'text-gold-soft' : 'text-ink' }}">
                تفاصيل التراخيص والاعتمادات
                <span aria-hidden="true">←</span>
            </a>
        </p>
    @endif
</div>
