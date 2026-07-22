<x-layouts.admin title="الصفحات الثابتة">

<p class="mb-6 rounded-sm bg-stone-soft/60 p-4 text-sm leading-relaxed text-charcoal-soft">
    هذه الصفحات مرتبطة بروابط ثابتة في الموقع، لذلك يمكن تعديل محتواها لكن لا
    يمكن حذفها — حذف إحداها كان سيُعطّل رابطاً منشوراً.
</p>

<div class="overflow-x-auto rounded-sm border border-stone bg-ivory">
    <table class="w-full min-w-[40rem] text-sm">
        <caption class="sr-only">قائمة الصفحات الثابتة</caption>
        <thead>
            <tr class="border-b border-stone bg-stone-soft/50 text-charcoal-soft">
                <th scope="col" class="px-5 py-3 text-start font-normal">الصفحة</th>
                <th scope="col" class="px-5 py-3 text-start font-normal">الفهرسة</th>
                <th scope="col" class="px-5 py-3 text-start font-normal">الحالة</th>
                <th scope="col" class="px-5 py-3 text-start font-normal"><span class="sr-only">إجراءات</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone">
            @foreach ($pages as $page)
                <tr>
                    <td class="px-5 py-4">
                        <a href="{{ route('admin.pages.edit', $page) }}"
                           class="font-display text-ink hover:text-gold-deep">{{ $page->title }}</a>
                        <span class="mt-1 block text-xs text-charcoal-soft" dir="ltr" style="text-align: start;">
                            /{{ $page->slug }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-charcoal-soft">{{ $page->is_indexable ? 'مسموحة' : 'noindex' }}</td>
                    <td class="px-5 py-4">
                        @if ($page->needs_review)
                            <span class="rounded-sm bg-gold/15 px-2.5 py-1 text-xs text-gold-deep">
                                بانتظار بيانات العميل
                            </span>
                        @else
                            <span class="text-charcoal-soft">مكتملة</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.pages.edit', $page) }}" class="text-gold-deep hover:underline">تحرير</a>
                            <a href="{{ $page->href() }}" target="_blank" rel="noopener"
                               class="text-charcoal-soft hover:underline">معاينة ↗</a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

</x-layouts.admin>
