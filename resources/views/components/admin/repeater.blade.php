@props([
    'name',
    'label',
    'rows' => [],
    'fields' => ['value' => 'نص'],
    'help' => null,
    'simple' => false,
])

@php
    // `simple` repeaters post a flat array (name[]); structured ones post
    // name[index][field]. The audience and scope lists are flat; process
    // steps and FAQs are structured.
    $template = $simple ? '' : array_fill_keys(array_keys($fields), '');
    $initial = old($name, $rows) ?: [];
@endphp

<div x-data="repeater(@js(array_values($initial)), @js($template))">
    <div class="flex items-baseline justify-between gap-4">
        <span class="font-display text-sm text-ink">{{ $label }}</span>
        <button type="button" @click="add()"
                class="rounded-sm border border-gold px-3 py-1.5 text-xs text-ink hover:bg-stone-soft">
            إضافة عنصر
        </button>
    </div>

    @if ($help)
        <p class="mt-1 text-xs leading-relaxed text-charcoal-soft">{{ $help }}</p>
    @endif

    <div class="mt-3 space-y-3">
        <template x-for="(row, index) in rows" :key="index">
            <div class="rounded-sm border border-stone bg-stone-soft/30 p-4">
                <div class="flex items-start gap-3">
                    <span class="mt-2.5 font-display text-xs text-charcoal-soft num" x-text="index + 1"></span>

                    <div class="min-w-0 flex-1 space-y-3">
                        @if ($simple)
                            <textarea :name="`{{ $name }}[${index}]`"
                                      x-model="rows[index]"
                                      rows="2"
                                      class="block w-full rounded-sm border border-stone bg-ivory px-3 py-2.5 text-sm
                                             focus:border-gold"></textarea>
                        @else
                            @foreach ($fields as $field => $fieldLabel)
                                <label class="block">
                                    <span class="block text-xs text-charcoal-soft">{{ $fieldLabel }}</span>
                                    <textarea :name="`{{ $name }}[${index}][{{ $field }}]`"
                                              x-model="rows[index].{{ $field }}"
                                              rows="{{ $loop->first ? 1 : 3 }}"
                                              class="mt-1 block w-full rounded-sm border border-stone bg-ivory px-3 py-2.5
                                                     text-sm focus:border-gold"></textarea>
                                </label>
                            @endforeach
                        @endif
                    </div>

                    {{-- Reordering matters: these lists render in order on the
                         public page, and the editor should not have to
                         retype rows to change their sequence. --}}
                    <div class="flex shrink-0 flex-col gap-1">
                        <button type="button" @click="move(index, -1)"
                                class="rounded-sm px-2 py-1 text-xs text-charcoal-soft hover:bg-stone">
                            <span aria-hidden="true">▲</span><span class="sr-only">تحريك لأعلى</span>
                        </button>
                        <button type="button" @click="move(index, 1)"
                                class="rounded-sm px-2 py-1 text-xs text-charcoal-soft hover:bg-stone">
                            <span aria-hidden="true">▼</span><span class="sr-only">تحريك لأسفل</span>
                        </button>
                        <button type="button" @click="remove(index)"
                                class="rounded-sm px-2 py-1 text-xs text-red-700 hover:bg-red-50">
                            <span aria-hidden="true">✕</span><span class="sr-only">حذف</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
