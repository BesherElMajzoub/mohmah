@php $isNew = ! $category->exists; @endphp

<x-layouts.admin :title="$isNew ? 'إضافة قسم' : 'تحرير: '.$category->title">

<form method="POST"
      action="{{ $isNew ? route('admin.post-categories.store') : route('admin.post-categories.update', $category) }}"
      class="max-w-2xl">
    @csrf
    @unless ($isNew) @method('PUT') @endunless

    <x-admin.panel title="بيانات القسم">
        <x-form.field name="title" label="اسم القسم" :value="$category->title" required />

        <x-form.field name="slug" label="الرابط (slug)" :value="$category->slug"
                      help="اتركه فارغاً ليُشتق من الاسم." />

        <x-form.field name="intro" label="نبذة تعريفية" type="textarea" rows="3" :value="$category->intro"
                      help="تظهر أعلى صفحة القسم." />

        <x-form.field name="seo_title" label="عنوان صفحة النتائج" :value="$category->seo_title" />
        <x-form.field name="seo_description" label="وصف صفحة النتائج" type="textarea" rows="3"
                      :value="$category->seo_description" />

        <x-form.field name="position" label="الترتيب" type="number" :value="$category->position ?? 0" dir="ltr" />

        <div class="flex gap-3 border-t border-stone pt-5">
            <button type="submit" class="rounded-sm bg-ink px-6 py-3 font-display text-sm text-ivory hover:bg-ink-700">
                {{ $isNew ? 'إنشاء' : 'حفظ' }}
            </button>
            <a href="{{ route('admin.post-categories.index') }}"
               class="rounded-sm border border-stone px-6 py-3 text-sm text-charcoal hover:bg-stone-soft">إلغاء</a>
        </div>
    </x-admin.panel>
</form>

</x-layouts.admin>
