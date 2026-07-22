@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'rows' => 4,
    'help' => null,
    'dir' => null,
])

@php
    $id = $attributes->get('id', $name);
    // Bracketed names (social[0][url]) need dot notation for old() and error
    // lookups, which is not the same string as the HTML name attribute.
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $hasError = $errors->has($key);
    $describedBy = collect([
        $help ? "{$id}-help" : null,
        $hasError ? "{$id}-error" : null,
    ])->filter()->implode(' ');
@endphp

<div>
    <label for="{{ $id }}" class="block font-display text-sm text-ink">
        {{ $label }}
        @if ($required)
            <span class="text-gold-deep" aria-hidden="true">*</span>
        @endif
    </label>

    @if ($help)
        <p id="{{ $id }}-help" class="mt-1 text-xs leading-relaxed text-charcoal-soft">{{ $help }}</p>
    @endif

    @php
        $classes = collect([
            'mt-2 block w-full rounded-sm border bg-ivory px-4 py-3 text-charcoal',
            'transition-colors placeholder:text-charcoal-soft/60',
            $hasError ? 'border-red-700' : 'border-stone focus:border-gold',
        ])->implode(' ');
    @endphp

    @if ($type === 'textarea')
        <textarea id="{{ $id }}"
                  name="{{ $name }}"
                  rows="{{ $rows }}"
                  @if ($required) required @endif
                  @if ($dir) dir="{{ $dir }}" @endif
                  @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                  @if ($hasError) aria-invalid="true" @endif
                  {{ $attributes->except('id')->class($classes) }}>{{ old($key, $value) }}</textarea>
    @else
        <input type="{{ $type }}"
               id="{{ $id }}"
               name="{{ $name }}"
               value="{{ old($key, $value) }}"
               @if ($required) required @endif
               @if ($dir) dir="{{ $dir }}" @endif
               @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
               @if ($hasError) aria-invalid="true" @endif
               {{ $attributes->except('id')->class($classes) }}>
    @endif

    @error($key)
        {{-- role="alert" so the message is announced when it appears, and
             aria-describedby ties it to the input for screen readers. --}}
        <p id="{{ $id }}-error" role="alert" class="mt-2 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
