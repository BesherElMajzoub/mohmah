@props(['name', 'label', 'value' => null, 'help' => null])

@php
    $id = $name.'-editor';
    $content = old($name, $value) ?? '';
@endphp

<div>
    <label for="{{ $id }}" class="block font-display text-sm text-ink">{{ $label }}</label>

    @if ($help)
        <p class="mt-1 text-xs leading-relaxed text-charcoal-soft">{{ $help }}</p>
    @endif

    <div x-data="richEditor(@js($content))"
         x-on:destroy="destroy()"
         class="mt-2 overflow-hidden rounded-sm border border-stone bg-ivory focus-within:border-gold">

        {{-- Toolbar. Buttons carry aria-pressed so assistive tech reports
             whether the format is active at the cursor, not just that a
             button exists. --}}
        <div class="flex flex-wrap items-center gap-0.5 border-b border-stone bg-stone-soft/50 p-2"
             role="toolbar" aria-label="أدوات التنسيق">

            @php
                $buttons = [
                    ['cmd' => 'bold',        'label' => 'عريض',        'text' => 'B',   'state' => 'bold'],
                    ['cmd' => 'italic',      'label' => 'مائل',        'text' => 'I',   'state' => 'italic'],
                    ['cmd' => 'h2',          'label' => 'عنوان رئيسي', 'text' => 'ع٢',  'state' => 'h2'],
                    ['cmd' => 'h3',          'label' => 'عنوان فرعي',  'text' => 'ع٣',  'state' => 'h3'],
                    ['cmd' => 'bulletList',  'label' => 'قائمة نقطية', 'text' => '•',   'state' => 'bulletList'],
                    ['cmd' => 'orderedList', 'label' => 'قائمة رقمية', 'text' => '١.',  'state' => 'orderedList'],
                    ['cmd' => 'blockquote',  'label' => 'اقتباس',      'text' => '❝',   'state' => 'blockquote'],
                    ['cmd' => 'hr',          'label' => 'فاصل',        'text' => '—',   'state' => null],
                ];
            @endphp

            @foreach ($buttons as $button)
                <button type="button"
                        @click="run(@js($button['cmd']))"
                        @if ($button['state'])
                            :aria-pressed="active.{{ $button['state'] }} ? 'true' : 'false'"
                            :class="active.{{ $button['state'] }} ? 'bg-ink text-ivory' : 'hover:bg-stone'"
                        @else
                            class="hover:bg-stone"
                        @endif
                        title="{{ $button['label'] }}"
                        class="min-w-9 rounded-sm px-2.5 py-1.5 font-display text-sm transition-colors">
                    <span aria-hidden="true">{{ $button['text'] }}</span>
                    <span class="sr-only">{{ $button['label'] }}</span>
                </button>
            @endforeach

            <span class="mx-1 h-5 w-px bg-stone" aria-hidden="true"></span>

            <button type="button" @click="setLink()"
                    :aria-pressed="active.link ? 'true' : 'false'"
                    :class="active.link ? 'bg-ink text-ivory' : 'hover:bg-stone'"
                    class="rounded-sm px-2.5 py-1.5 text-sm transition-colors" title="رابط">
                <span aria-hidden="true">🔗</span><span class="sr-only">إدراج رابط</span>
            </button>

            {{-- Image insertion goes through the media library so every image
                 has Arabic alt text recorded against it. --}}
            <button type="button"
                    @click="$dispatch('open-media-picker', { target: @js($id) })"
                    class="rounded-sm px-2.5 py-1.5 text-sm transition-colors hover:bg-stone" title="صورة">
                <span aria-hidden="true">🖼</span><span class="sr-only">إدراج صورة</span>
            </button>

            <span class="mx-1 h-5 w-px bg-stone" aria-hidden="true"></span>

            <button type="button" @click="run('table')"
                    class="rounded-sm px-2.5 py-1.5 text-sm hover:bg-stone" title="جدول">
                <span aria-hidden="true">▦</span><span class="sr-only">إدراج جدول</span>
            </button>
            <button type="button" @click="run('addRow')"
                    class="rounded-sm px-2.5 py-1.5 text-xs hover:bg-stone">+صف</button>
            <button type="button" @click="run('addColumn')"
                    class="rounded-sm px-2.5 py-1.5 text-xs hover:bg-stone">+عمود</button>

            <span class="ms-auto flex gap-0.5">
                <button type="button" @click="run('undo')"
                        class="rounded-sm px-2.5 py-1.5 text-sm hover:bg-stone" title="تراجع">
                    <span aria-hidden="true">↷</span><span class="sr-only">تراجع</span>
                </button>
                <button type="button" @click="run('redo')"
                        class="rounded-sm px-2.5 py-1.5 text-sm hover:bg-stone" title="إعادة">
                    <span aria-hidden="true">↶</span><span class="sr-only">إعادة</span>
                </button>
            </span>
        </div>

        {{-- The editing surface. TipTap mounts here. --}}
        <div x-ref="editor" id="{{ $id }}"></div>

        {{-- The actual submitted value. Present in the DOM with the real
             content before any JavaScript runs, so the field round-trips
             correctly even if the editor fails to initialise. --}}
        <textarea x-ref="input" name="{{ $name }}" class="hidden" aria-hidden="true" tabindex="-1">{{ $content }}</textarea>
    </div>
</div>
