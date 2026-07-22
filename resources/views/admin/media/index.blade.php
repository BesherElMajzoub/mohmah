<x-layouts.admin title="مكتبة الوسائط">

<form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data"
      class="rounded-sm border border-stone bg-ivory p-6">
    @csrf

    <h2 class="font-display text-lg text-ink">رفع صورة</h2>

    <div class="mt-5 grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
        <div>
            <label for="file" class="block text-sm text-charcoal-soft">الملف</label>
            <input type="file" id="file" name="file" required accept="image/jpeg,image/png,image/webp,image/avif"
                   class="mt-1.5 w-full rounded-sm border border-stone bg-ivory px-3 py-2.5 text-sm">
            <p class="mt-1 text-xs text-charcoal-soft">JPG أو PNG أو WebP أو AVIF، حتى 5 ميجابايت.</p>
        </div>

        <div>
            <label for="alt_ar" class="block text-sm text-charcoal-soft">النص البديل بالعربية</label>
            <input type="text" id="alt_ar" name="alt_ar"
                   class="mt-1.5 w-full rounded-sm border border-stone bg-ivory px-3 py-2.5 text-sm">
            <p class="mt-1 text-xs text-charcoal-soft">وصف موجز لما تُظهره الصورة.</p>
        </div>

        <button type="submit" class="rounded-sm bg-ink px-6 py-3 font-display text-sm text-ivory hover:bg-ink-700">
            رفع
        </button>
    </div>
</form>

@if ($media->isEmpty())
    <p class="mt-8 rounded-sm border border-stone bg-ivory p-10 text-center text-charcoal-soft">
        لا توجد ملفات في المكتبة بعد.
    </p>
@else
    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($media as $medium)
            <div class="overflow-hidden rounded-sm border border-stone bg-ivory">
                <img src="{{ $medium->url() }}"
                     alt="{{ $medium->alt_ar }}"
                     width="{{ $medium->width }}" height="{{ $medium->height }}"
                     loading="lazy" decoding="async"
                     class="aspect-[4/3] w-full bg-stone-soft object-cover">

                <div class="p-4">
                    <p class="truncate text-xs text-charcoal-soft" dir="ltr" style="text-align: start;">
                        {{ $medium->original_name }}
                    </p>

                    @if ($medium->width)
                        <p class="mt-1 text-xs text-charcoal-soft num">
                            {{ $medium->width }} × {{ $medium->height }}
                        </p>
                    @endif

                    @if ($medium->isMissingAlt())
                        {{-- Flagged rather than blocked at upload: an image
                             without alt text is unusable by screen readers and
                             loses its search value, but refusing the upload
                             would just make people write filler. --}}
                        <p class="mt-2 rounded-sm bg-gold/15 px-2 py-1 text-xs text-gold-deep">
                            ينقصها نص بديل
                        </p>
                    @endif

                    <form method="POST" action="{{ route('admin.media.update', $medium) }}" class="mt-3 space-y-2">
                        @csrf
                        @method('PUT')
                        <label class="block">
                            <span class="sr-only">النص البديل</span>
                            <input type="text" name="alt_ar" value="{{ $medium->alt_ar }}" placeholder="النص البديل"
                                   class="w-full rounded-sm border border-stone bg-ivory px-2.5 py-2 text-xs">
                        </label>
                        <label class="block">
                            <span class="sr-only">التسمية التوضيحية</span>
                            <input type="text" name="caption_ar" value="{{ $medium->caption_ar }}" placeholder="تسمية توضيحية (اختياري)"
                                   class="w-full rounded-sm border border-stone bg-ivory px-2.5 py-2 text-xs">
                        </label>
                        <button type="submit" class="w-full rounded-sm bg-ink px-3 py-2 text-xs text-ivory hover:bg-ink-700">
                            حفظ
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.media.destroy', $medium) }}" class="mt-2"
                          onsubmit="return confirm('سيُحذف الملف نهائياً. هل أنت متأكد؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-sm border border-stone px-3 py-2 text-xs text-red-700 hover:bg-red-50">
                            حذف
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">{{ $media->links() }}</div>
@endif

</x-layouts.admin>
