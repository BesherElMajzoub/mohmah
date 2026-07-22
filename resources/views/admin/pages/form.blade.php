@php
    use App\Support\Content;
    $needsInfo = Content::needsConfirmation($page->body);
@endphp

<x-layouts.admin :title="'تحرير: '.$page->title">

<x-slot:actions>
    <a href="{{ $page->href() }}" target="_blank" rel="noopener"
       class="rounded-sm border border-stone px-5 py-2.5 text-sm text-charcoal hover:bg-stone-soft">
        معاينة ↗
    </a>
</x-slot:actions>

@if ($needsInfo)
    {{-- The marker is visible here and only here. Content::public() strips it
         from every public render, and a feature test asserts it never
         escapes. --}}
    <div class="mb-6 rounded-sm border-s-4 border-gold bg-ivory p-5 text-sm leading-relaxed text-charcoal">
        <p class="font-display text-ink">هذه الصفحة تنتظر معلومات منك</p>
        <p class="mt-2">
            يحتوي النص على علامة <code dir="ltr" class="rounded bg-stone-soft px-1.5 py-0.5 text-xs">[[NEEDS_CLIENT_CONFIRMATION]]</code>
            تشير إلى معلومة لم تُزوَّد بعد. هذه العلامة والفقرة التي تحتويها
            <strong>لا تظهران للزوار إطلاقاً</strong> — لم يُكتب أي بديل مُفترض عنها.
            استبدل الفقرة بالمعلومة الصحيحة عند توفرها.
        </p>
    </div>
@endif

<form method="POST" action="{{ route('admin.pages.update', $page) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="space-y-6">
            <x-admin.panel title="المحتوى">
                <x-form.field name="title" label="عنوان الصفحة" :value="$page->title" required />
                <x-form.field name="h1" label="العنوان الرئيسي (H1)" :value="$page->h1" required />
                <x-form.field name="intro" label="المقدمة" type="textarea" rows="3" :value="$page->intro" />

                <x-admin.editor name="body" label="نص الصفحة" :value="$page->body" />
            </x-admin.panel>
        </div>

        <div class="space-y-6">
            <x-admin.panel title="الرابط">
                <p class="text-sm text-charcoal-soft">
                    رابط هذه الصفحة ثابت ومرتبط بمسار منشور:
                </p>
                <p class="rounded-sm bg-stone-soft/60 p-3 text-sm" dir="ltr" style="text-align: start;">
                    /{{ $page->slug }}
                </p>

                <div class="flex gap-3 border-t border-stone pt-5">
                    <button type="submit"
                            class="flex-1 rounded-sm bg-ink px-5 py-3 font-display text-sm text-ivory hover:bg-ink-700">
                        حفظ
                    </button>
                    <a href="{{ route('admin.pages.index') }}"
                       class="rounded-sm border border-stone px-5 py-3 text-sm text-charcoal hover:bg-stone-soft">إلغاء</a>
                </div>
            </x-admin.panel>

            <x-admin.panel title="تحسين الظهور (SEO)">
                <x-form.field name="seo_title" label="عنوان صفحة النتائج" :value="$page->seo_title" />
                <x-form.field name="seo_description" label="وصف صفحة النتائج" type="textarea" rows="3"
                              :value="$page->seo_description" />
                <x-form.field name="canonical_url" label="الرابط القياسي" :value="$page->canonical_url" dir="ltr" />

                <label class="flex items-start gap-2.5 text-sm">
                    <input type="checkbox" name="is_indexable" value="1" class="mt-1 size-4 rounded-sm border-stone"
                           @checked(old('is_indexable', $page->is_indexable))>
                    <span>
                        <span class="block text-ink">السماح بفهرسة الصفحة</span>
                        <span class="block text-xs text-charcoal-soft">
                            صفحتا الخصوصية والشروط تُترك فهرستهما معطّلة عادةً.
                        </span>
                    </span>
                </label>
            </x-admin.panel>
        </div>
    </div>
</form>

</x-layouts.admin>
