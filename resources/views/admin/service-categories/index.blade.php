<x-layouts.admin title="مجالات الخدمات">

<x-slot:actions>
    <a href="{{ route('admin.service-categories.create') }}"
       class="rounded-sm bg-ink px-5 py-2.5 font-display text-sm text-ivory hover:bg-ink-700">
        إضافة مجال
    </a>
</x-slot:actions>

<p class="mb-6 rounded-sm bg-stone-soft/60 p-4 text-sm leading-relaxed text-charcoal-soft">
    المجالات تجمّع الخدمات في القائمة الرئيسية وصفحة الخدمات. لا توجد لها صفحات
    مستقلة — الروابط تشير إلى القسم المقابل داخل صفحة الخدمات، تفادياً لصفحات
    مكرّرة ضعيفة المحتوى.
</p>

<div class="overflow-x-auto rounded-sm border border-stone bg-ivory">
    <table class="w-full min-w-[40rem] text-sm">
        <caption class="sr-only">قائمة مجالات الخدمات</caption>
        <thead>
            <tr class="border-b border-stone bg-stone-soft/50 text-charcoal-soft">
                <th scope="col" class="px-5 py-3 text-start font-normal">المجال</th>
                <th scope="col" class="px-5 py-3 text-start font-normal">اسم القائمة</th>
                <th scope="col" class="px-5 py-3 text-start font-normal">عدد الخدمات</th>
                <th scope="col" class="px-5 py-3 text-start font-normal">الترتيب</th>
                <th scope="col" class="px-5 py-3 text-start font-normal"><span class="sr-only">إجراءات</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone">
            @foreach ($categories as $category)
                <tr>
                    <td class="px-5 py-4">
                        <a href="{{ route('admin.service-categories.edit', $category) }}"
                           class="font-display text-ink hover:text-gold-deep">{{ $category->title }}</a>
                        <span class="mt-1 block text-xs text-charcoal-soft" dir="ltr" style="text-align: start;">
                            {{ $category->slug }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-charcoal-soft">{{ $category->menu_label ?? '—' }}</td>
                    <td class="px-5 py-4 num">{{ $category->services_count }}</td>
                    <td class="px-5 py-4 num">{{ $category->position }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.service-categories.edit', $category) }}"
                               class="text-gold-deep hover:underline">تحرير</a>

                            @if ($category->services_count === 0)
                                <form method="POST" action="{{ route('admin.service-categories.destroy', $category) }}"
                                      onsubmit="return confirm('هل تريد حذف هذا المجال؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-700 hover:underline">حذف</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

</x-layouts.admin>
