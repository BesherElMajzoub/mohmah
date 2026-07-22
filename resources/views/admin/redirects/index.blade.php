<x-layouts.admin title="التحويلات (301)">

<x-slot:actions>
    <a href="{{ route('admin.redirects.create') }}"
       class="rounded-sm bg-ink px-5 py-2.5 font-display text-sm text-ivory hover:bg-ink-700">
        إضافة تحويل
    </a>
</x-slot:actions>

<p class="mb-6 rounded-sm bg-stone-soft/60 p-4 text-sm leading-relaxed text-charcoal-soft">
    وجّه كل رابط قديم إلى أقرب صفحة جديدة مناسبة له فعلاً. تحويل كل الروابط
    القديمة إلى الصفحة الرئيسية يُفقد قيمتها في محركات البحث. الرابط الذي لا
    يوجد له بديل مناسب يُعطى الرمز 410 لا 404، لإخبار محرك البحث بأن إزالته
    مقصودة.
    عمود «الطلبات» يُظهر بعد الإطلاق أي الروابط القديمة ما زالت تُطلب فعلاً.
</p>

<form method="GET" class="mb-6 rounded-sm border border-stone bg-ivory p-5">
    <label for="q" class="block text-sm text-charcoal-soft">بحث في الروابط</label>
    <div class="mt-1.5 flex gap-3">
        <input type="search" id="q" name="q" value="{{ request('q') }}" dir="ltr"
               class="flex-1 rounded-sm border border-stone bg-ivory px-3 py-2.5 text-sm">
        <button type="submit" class="rounded-sm bg-ink px-5 py-2.5 font-display text-sm text-ivory hover:bg-ink-700">
            بحث
        </button>
    </div>
</form>

<div class="overflow-x-auto rounded-sm border border-stone bg-ivory">
    <table class="w-full min-w-[52rem] text-sm">
        <caption class="sr-only">قائمة تحويلات الروابط</caption>
        <thead>
            <tr class="border-b border-stone bg-stone-soft/50 text-charcoal-soft">
                <th scope="col" class="px-5 py-3 text-start font-normal">من</th>
                <th scope="col" class="px-5 py-3 text-start font-normal">إلى</th>
                <th scope="col" class="px-5 py-3 text-start font-normal">الرمز</th>
                <th scope="col" class="px-5 py-3 text-start font-normal">الطلبات</th>
                <th scope="col" class="px-5 py-3 text-start font-normal">الحالة</th>
                <th scope="col" class="px-5 py-3 text-start font-normal"><span class="sr-only">إجراءات</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone">
            @forelse ($redirects as $redirect)
                <tr>
                    <td class="px-5 py-4">
                        <span class="block max-w-xs truncate" dir="ltr" style="text-align: start;">
                            {{ $redirect->from_path }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="block max-w-xs truncate text-charcoal-soft" dir="ltr" style="text-align: start;">
                            {{ $redirect->to_path ?? '—' }}
                        </span>
                    </td>
                    <td class="px-5 py-4 num">{{ $redirect->status_code }}</td>
                    <td class="px-5 py-4 num">{{ $redirect->hits }}</td>
                    <td class="px-5 py-4">
                        <span @class([
                            'rounded-sm px-2.5 py-1 text-xs',
                            'bg-ink text-ivory' => $redirect->is_active,
                            'bg-stone text-charcoal' => ! $redirect->is_active,
                        ])>{{ $redirect->is_active ? 'مفعّل' : 'معطّل' }}</span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.redirects.edit', $redirect) }}"
                               class="text-gold-deep hover:underline">تحرير</a>
                            <form method="POST" action="{{ route('admin.redirects.destroy', $redirect) }}"
                                  onsubmit="return confirm('هل تريد حذف هذا التحويل؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 hover:underline">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-charcoal-soft">
                        لا توجد تحويلات بعد. أضِف روابط الموقع القديم قبل الإطلاق.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $redirects->links() }}</div>

</x-layouts.admin>
