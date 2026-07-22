@php
    $isNew = ! $post->exists;
@endphp

<x-layouts.admin :title="$isNew ? 'مقال جديد' : 'تحرير: '.$post->title">

<x-slot:actions>
    @if (! $isNew && $post->isPublished())
        <a href="{{ $post->href() }}" target="_blank" rel="noopener"
           class="rounded-sm border border-stone px-5 py-2.5 text-sm text-charcoal hover:bg-stone-soft">
            معاينة المقال ↗
        </a>
    @endif
</x-slot:actions>

<form method="POST"
      action="{{ $isNew ? route('admin.posts.store') : route('admin.posts.update', $post) }}"
      class="space-y-6">
    @csrf
    @unless ($isNew) @method('PUT') @endunless

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">

        <div class="space-y-6">
            <x-admin.panel title="الأساسيات">
                <x-form.field name="title" label="عنوان المقال" :value="$post->title" required />

                <x-form.field name="h1" label="العنوان في الصفحة (اختياري)" :value="$post->h1"
                              help="اتركه فارغاً لاستخدام عنوان المقال." />

                <div x-data="slugField(@js(old('slug', $post->slug)), '/المدونة')">
                    <label for="slug" class="block font-display text-sm text-ink">الرابط (slug)</label>
                    <p class="mt-1 text-xs text-charcoal-soft">اتركه فارغاً ليُشتق من العنوان.</p>
                    <input type="text" id="slug" name="slug" x-model="slug"
                           class="mt-2 block w-full rounded-sm border border-stone bg-ivory px-4 py-3 text-sm focus:border-gold">
                    <p class="mt-2 text-xs text-charcoal-soft" dir="ltr" style="text-align: start;">
                        <span x-text="preview"></span>
                    </p>
                </div>

                <x-form.field name="excerpt" label="المقتطف" type="textarea" rows="3" :value="$post->excerpt"
                              help="يظهر في قائمة المدونة وفي بطاقة المشاركة." />
            </x-admin.panel>

            <x-admin.panel title="نص المقال">
                <x-admin.editor name="body" label="المحتوى" :value="$post->body"
                                help="ابدأ العناوين الداخلية من المستوى الثاني (ع٢). عند الاستشهاد بحكم نظامي، اذكر المصدر الرسمي وتاريخ نسخته." />
            </x-admin.panel>

            <x-admin.panel title="الروابط الداخلية"
                           description="ربط المقال بالخدمات ذات الصلة هو أهم رابط تجارياً — ينقل القارئ من السؤال إلى الخدمة.">
                <label class="block">
                    <span class="block font-display text-sm text-ink">خدمات ذات صلة</span>
                    <select name="service_ids[]" multiple size="8"
                            class="mt-2 w-full rounded-sm border border-stone bg-ivory px-3 py-2 text-sm">
                        @foreach ($allServices as $option)
                            <option value="{{ $option->id }}"
                                @selected(in_array($option->id, old('service_ids', $post->services->pluck('id')->all()), true))>
                                {{ $option->title }}
                            </option>
                        @endforeach
                    </select>
                </label>

                @if ($allPosts->isNotEmpty())
                    <label class="block">
                        <span class="block font-display text-sm text-ink">مقالات ذات صلة</span>
                        <select name="related_post_ids[]" multiple size="6"
                                class="mt-2 w-full rounded-sm border border-stone bg-ivory px-3 py-2 text-sm">
                            @foreach ($allPosts as $option)
                                @continue($option->id === $post->id)
                                <option value="{{ $option->id }}"
                                    @selected(in_array($option->id, old('related_post_ids', $post->relatedPosts->pluck('id')->all()), true))>
                                    {{ $option->title }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                @endif
            </x-admin.panel>
        </div>

        <div class="space-y-6">
            <x-admin.panel title="النشر">
                <label class="block">
                    <span class="block font-display text-sm text-ink">الحالة</span>
                    <select name="status" class="mt-2 w-full rounded-sm border border-stone bg-ivory px-4 py-3 text-sm">
                        <option value="draft" @selected(old('status', $post->status) === 'draft')>مسودة</option>
                        <option value="scheduled" @selected(old('status', $post->status) === 'scheduled')>مجدول</option>
                        <option value="published" @selected(old('status', $post->status) === 'published')>منشور</option>
                    </select>
                </label>

                <x-form.field name="published_at" label="تاريخ النشر" type="datetime-local"
                              :value="$post->published_at?->format('Y-m-d\TH:i')" dir="ltr"
                              help="مطلوب للمقال المجدول. عند حلول التاريخ يظهر المقال تلقائياً." />

                <x-form.field name="content_updated_at" label="تاريخ آخر تحديث للمحتوى" type="datetime-local"
                              :value="$post->content_updated_at?->format('Y-m-d\TH:i')" dir="ltr"
                              help="عدّله عند تحديث جوهري في المحتوى، لا عند تصحيح إملائي." />

                <label class="block">
                    <span class="block font-display text-sm text-ink">القسم</span>
                    <select name="post_category_id" required
                            class="mt-2 w-full rounded-sm border border-stone bg-ivory px-4 py-3 text-sm">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                @selected(old('post_category_id', $post->post_category_id) == $category->id)>
                                {{ $category->title }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <div class="flex gap-3 border-t border-stone pt-5">
                    <button type="submit"
                            class="flex-1 rounded-sm bg-ink px-5 py-3 font-display text-sm text-ivory hover:bg-ink-700">
                        {{ $isNew ? 'إنشاء' : 'حفظ' }}
                    </button>
                    <a href="{{ route('admin.posts.index') }}"
                       class="rounded-sm border border-stone px-5 py-3 text-sm text-charcoal hover:bg-stone-soft">
                        إلغاء
                    </a>
                </div>

                <p class="rounded-sm bg-stone-soft/60 p-3 text-xs leading-relaxed text-charcoal-soft">
                    بمجرد الحفظ كمنشور يُضاف المقال إلى <span dir="ltr">sitemap.xml</span> فوراً.
                </p>
            </x-admin.panel>

            <x-admin.panel title="النسبة والمراجعة"
                           description="اسم الكاتب واسم المراجع يظهران للقارئ. لا تضع اسم مراجع لم يراجع المقال فعلاً — هذه هي الإشارة التي تدل على إشراف قانوني.">
                <x-form.field name="author_name" label="بقلم" :value="$post->author_name" />
                <x-form.field name="reviewer_name" label="مراجعة" :value="$post->reviewer_name" />
            </x-admin.panel>

            <x-admin.panel title="الصورة الرئيسية">
                @if ($post->coverImage)
                    <img src="{{ $post->coverImage->url() }}" alt="{{ $post->coverImage->alt_ar }}"
                         class="w-full rounded-sm border border-stone">
                @endif

                <label class="block">
                    <span class="block font-display text-sm text-ink">اختر من مكتبة الوسائط</span>
                    <select name="cover_image_id" class="mt-2 w-full rounded-sm border border-stone bg-ivory px-4 py-3 text-sm">
                        <option value="">بدون صورة</option>
                        @foreach (\App\Models\Media::query()->latest()->limit(100)->get() as $medium)
                            <option value="{{ $medium->id }}" @selected(old('cover_image_id', $post->cover_image_id) == $medium->id)>
                                {{ $medium->original_name }}{{ $medium->isMissingAlt() ? ' — بلا نص بديل' : '' }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <p class="text-xs text-charcoal-soft">
                    <a href="{{ route('admin.media.index') }}" class="text-gold-deep hover:underline">رفع صورة جديدة ←</a>
                </p>
            </x-admin.panel>

            <x-admin.panel title="تحسين الظهور (SEO)">
                <x-form.field name="seo_title" label="عنوان صفحة النتائج" :value="$post->seo_title" />
                <x-form.field name="seo_description" label="وصف صفحة النتائج" type="textarea" rows="3"
                              :value="$post->seo_description" />
                <x-form.field name="focus_phrase" label="العبارة المستهدفة" :value="$post->focus_phrase" />
                <x-form.field name="canonical_url" label="الرابط القياسي" :value="$post->canonical_url" dir="ltr"
                              help="اتركه فارغاً — يُبنى تلقائياً." />

                <label class="flex items-start gap-2.5 text-sm">
                    <input type="checkbox" name="is_indexable" value="1" class="mt-1 size-4 rounded-sm border-stone"
                           @checked(old('is_indexable', $post->is_indexable ?? true))>
                    <span class="text-ink">السماح بفهرسة المقال</span>
                </label>
            </x-admin.panel>
        </div>
    </div>
</form>

</x-layouts.admin>
