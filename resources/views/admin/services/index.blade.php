<x-layouts.admin title="الخدمات">

<x-slot:actions>
    <a href="{{ route('admin.services.create') }}"
       class="rounded-sm bg-ink px-5 py-2.5 font-display text-sm text-ivory hover:bg-ink-700">
        إضافة خدمة
    </a>
</x-slot:actions>

<form method="GET" class="rounded-sm border border-stone bg-ivory p-5">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="lg:col-span-2">
            <label for="q" class="block text-sm text-charcoal-soft">بحث بالعنوان</label>
            <input type="search" id="q" name="q" value="{{ request('q') }}"
                   class="mt-1.5 w-full rounded-sm border border-stone bg-ivory px-3 py-2.5 text-sm">
        </div>
        <div>
            <label for="category" class="block text-sm text-charcoal-soft">المجال</label>
            <select id="category" name="category" class="mt-1.5 w-full rounded-sm border border-stone bg-ivory px-3 py-2.5 text-sm">
                <option value="">الكل</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request()->integer('category') === $category->id)>
                        {{ $category->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="block text-sm text-charcoal-soft">الحالة</label>
            <select id="status" name="status" class="mt-1.5 w-full rounded-sm border border-stone bg-ivory px-3 py-2.5 text-sm">
                <option value="">الكل</option>
                <option value="published" @selected(request('status') === 'published')>منشور</option>
                <option value="draft" @selected(request('status') === 'draft')>مسودة</option>
            </select>
        </div>
    </div>
    <button type="submit" class="mt-4 rounded-sm bg-ink px-5 py-2.5 font-display text-sm text-ivory hover:bg-ink-700">
        تصفية
    </button>
</form>

<div class="mt-6 overflow-x-auto rounded-sm border border-stone bg-ivory">
    <table class="w-full min-w-[52rem] text-sm">
        <caption class="sr-only">قائمة الخدمات</caption>
        <thead>
            <tr class="border-b border-stone bg-stone-soft/50 text-charcoal-soft">
                <th scope="col" class="px-5 py-3 text-start font-normal">الخدمة</th>
                <th scope="col" class="px-5 py-3 text-start font-normal">المجال</th>
                <th scope="col" class="px-5 py-3 text-start font-normal">الحالة</th>
                <th scope="col" class="px-5 py-3 text-start font-normal">الفهرسة</th>
                <th scope="col" class="px-5 py-3 text-start font-normal"><span class="sr-only">إجراءات</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone">
            @forelse ($services as $service)
                <tr>
                    <td class="px-5 py-4">
                        <a href="{{ route('admin.services.edit', $service) }}" class="font-display text-ink hover:text-gold-deep">
                            {{ $service->title }}
                        </a>
                        <span class="mt-1 block text-xs text-charcoal-soft" dir="ltr" style="text-align: start;">
                            /خدمات/{{ $service->slug }}
                        </span>
                        @if ($service->needs_review)
                            <span class="mt-1.5 inline-block rounded-sm bg-gold/15 px-2 py-0.5 text-xs text-gold-deep">
                                بانتظار المراجعة القانونية
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-charcoal-soft">{{ $service->category->title }}</td>
                    <td class="px-5 py-4">
                        <span @class([
                            'rounded-sm px-2.5 py-1 text-xs',
                            'bg-ink text-ivory' => $service->isPublished(),
                            'bg-stone text-charcoal' => ! $service->isPublished(),
                        ])>{{ $service->statusLabel() }}</span>
                    </td>
                    <td class="px-5 py-4 text-charcoal-soft">
                        {{ $service->is_indexable ? 'مسموحة' : 'noindex' }}
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.services.edit', $service) }}" class="text-gold-deep hover:underline">تحرير</a>

                            @if ($service->isPublished())
                                <a href="{{ $service->href() }}" target="_blank" rel="noopener"
                                   class="text-charcoal-soft hover:underline">معاينة ↗</a>
                            @endif

                            <form method="POST" action="{{ route('admin.services.destroy', $service) }}"
                                  onsubmit="return confirm('سيتم حذف هذه الخدمة نهائياً. هل أنت متأكد؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 hover:underline">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-charcoal-soft">لا توجد خدمات مطابقة.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $services->links() }}</div>

</x-layouts.admin>
