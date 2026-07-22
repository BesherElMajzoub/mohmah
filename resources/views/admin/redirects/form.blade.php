@php $isNew = ! $redirect->exists; @endphp

<x-layouts.admin :title="$isNew ? 'إضافة تحويل' : 'تحرير تحويل'">

<form method="POST"
      action="{{ $isNew ? route('admin.redirects.store') : route('admin.redirects.update', $redirect) }}"
      class="max-w-2xl">
    @csrf
    @unless ($isNew) @method('PUT') @endunless

    <x-admin.panel title="بيانات التحويل">
        <x-form.field name="from_path" label="الرابط القديم" :value="$redirect->from_path" required dir="ltr"
                      help="المسار فقط دون اسم النطاق، مثل /old-page. يُدعم العربي، وتُوحَّد صيغته تلقائياً." />

        <x-form.field name="to_path" label="الوجهة الجديدة" :value="$redirect->to_path" dir="ltr"
                      help="مسار داخلي مثل /خدمات/التحكيم-التجاري، أو رابط كامل لموقع آخر. يُترك فارغاً مع الرمز 410." />

        <label class="block">
            <span class="block font-display text-sm text-ink">رمز الاستجابة</span>
            <select name="status_code" class="mt-2 w-full rounded-sm border border-stone bg-ivory px-4 py-3 text-sm">
                <option value="301" @selected(old('status_code', $redirect->status_code) == 301)>
                    301 — نقل دائم (الخيار الصحيح في أغلب الحالات)
                </option>
                <option value="302" @selected(old('status_code', $redirect->status_code) == 302)>
                    302 — نقل مؤقت
                </option>
                <option value="410" @selected(old('status_code', $redirect->status_code) == 410)>
                    410 — محذوفة نهائياً بلا بديل
                </option>
            </select>
            <span class="mt-1.5 block text-xs leading-relaxed text-charcoal-soft">
                استخدم 301 لنقل قيمة الرابط القديم إلى الجديد. لا تستخدم 302 إلا لتحويل
                مؤقت فعلاً، فهي لا تنقل قيمة الرابط.
            </span>
        </label>

        <x-form.field name="note" label="ملاحظة داخلية" :value="$redirect->note"
                      help="لتذكيرك بسبب هذا التحويل. لا تظهر للزوار." />

        <label class="flex items-start gap-2.5 text-sm">
            <input type="checkbox" name="is_active" value="1" class="mt-1 size-4 rounded-sm border-stone"
                   @checked(old('is_active', $redirect->is_active ?? true))>
            <span class="text-ink">التحويل مفعّل</span>
        </label>

        <div class="flex gap-3 border-t border-stone pt-5">
            <button type="submit" class="rounded-sm bg-ink px-6 py-3 font-display text-sm text-ivory hover:bg-ink-700">
                {{ $isNew ? 'إضافة' : 'حفظ' }}
            </button>
            <a href="{{ route('admin.redirects.index') }}"
               class="rounded-sm border border-stone px-6 py-3 text-sm text-charcoal hover:bg-stone-soft">إلغاء</a>
        </div>
    </x-admin.panel>
</form>

</x-layouts.admin>
