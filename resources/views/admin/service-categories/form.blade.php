@php $isNew = ! $category->exists; @endphp

<x-layouts.admin :title="$isNew ? 'إضافة مجال' : 'تحرير: '.$category->title">

<form method="POST"
      action="{{ $isNew ? route('admin.service-categories.store') : route('admin.service-categories.update', $category) }}"
      class="max-w-2xl">
    @csrf
    @unless ($isNew) @method('PUT') @endunless

    <x-admin.panel title="بيانات المجال">
        <x-form.field name="title" label="اسم المجال" :value="$category->title" required />

        <x-form.field name="menu_label" label="اسم مختصر للقائمة" :value="$category->menu_label"
                      help="يُستخدم في القائمة الرئيسية حين يكون الاسم الكامل طويلاً. اتركه فارغاً لاستخدام الاسم الكامل." />

        <x-form.field name="slug" label="المعرّف (slug)" :value="$category->slug"
                      help="يُستخدم كمرساة داخل صفحة الخدمات. اتركه فارغاً ليُشتق من الاسم." />

        <x-form.field name="intro" label="نبذة تعريفية" type="textarea" rows="3" :value="$category->intro"
                      help="تظهر في القائمة المنسدلة وفي صفحة الخدمات." />

        <x-form.field name="position" label="الترتيب" type="number" :value="$category->position ?? 0" dir="ltr" />

        <div class="flex gap-3 border-t border-stone pt-5">
            <button type="submit" class="rounded-sm bg-ink px-6 py-3 font-display text-sm text-ivory hover:bg-ink-700">
                {{ $isNew ? 'إنشاء' : 'حفظ' }}
            </button>
            <a href="{{ route('admin.service-categories.index') }}"
               class="rounded-sm border border-stone px-6 py-3 text-sm text-charcoal hover:bg-stone-soft">إلغاء</a>
        </div>
    </x-admin.panel>
</form>

</x-layouts.admin>
