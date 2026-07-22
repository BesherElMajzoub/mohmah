@php
    $isNew = ! $service->exists;
@endphp

<x-layouts.admin :title="$isNew ? 'إضافة خدمة' : 'تحرير: '.$service->title">

<x-slot:actions>
    @if (! $isNew && $service->isPublished())
        <a href="{{ $service->href() }}" target="_blank" rel="noopener"
           class="rounded-sm border border-stone px-5 py-2.5 text-sm text-charcoal hover:bg-stone-soft">
            معاينة الصفحة ↗
        </a>
    @endif
</x-slot:actions>

@if ($service->needs_review)
    {{-- Admin-only. The public page never renders this, and never renders
         the marker that triggers it. --}}
    <div class="mb-6 rounded-sm border-s-4 border-gold bg-ivory p-4 text-sm leading-relaxed text-charcoal">
        نص هذه الصفحة مكتوب استناداً إلى نطاق الخدمة المزوّد، ويحتاج مراجعة قانونية
        قبل اعتماده نهائياً. أزل علامة «يحتاج مراجعة» بعد اعتماد الصياغة.
    </div>
@endif

<form method="POST"
      action="{{ $isNew ? route('admin.services.store') : route('admin.services.update', $service) }}"
      class="space-y-6">
    @csrf
    @unless ($isNew) @method('PUT') @endunless

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">

        {{-- Main column ------------------------------------------------- --}}
        <div class="space-y-6">

            <x-admin.panel title="الأساسيات">
                <x-form.field name="title" label="عنوان الخدمة" :value="$service->title" required
                              help="يظهر في القوائم والبطاقات." />

                <x-form.field name="h1" label="العنوان الرئيسي في الصفحة (H1)" :value="$service->h1" required
                              help="العنوان الوحيد من مستوى H1 في الصفحة. قد يختلف عن عنوان القائمة." />

                <div x-data="slugField(@js(old('slug', $service->slug)), '/خدمات')">
                    <label for="slug" class="block font-display text-sm text-ink">الرابط (slug)</label>
                    <p class="mt-1 text-xs text-charcoal-soft">
                        اتركه فارغاً ليُشتق من العنوان الرئيسي. يُدعم العربي، وتُزال التشكيل والتطويل تلقائياً.
                    </p>
                    <input type="text" id="slug" name="slug" x-model="slug"
                           class="mt-2 block w-full rounded-sm border border-stone bg-ivory px-4 py-3 text-sm focus:border-gold">
                    <p class="mt-2 text-xs text-charcoal-soft" dir="ltr" style="text-align: start;">
                        <span x-text="preview"></span>
                    </p>
                </div>

                <x-form.field name="summary" label="ملخص قصير" type="textarea" rows="2" :value="$service->summary"
                              help="سطر واحد يظهر في بطاقة الخدمة وفي القائمة المنسدلة." />
            </x-admin.panel>

            <x-admin.panel title="نص الخدمة">
                <x-admin.editor name="overview" label="نظرة عامة" :value="$service->overview"
                                help="اكتب نصاً أصلياً لهذه الخدمة تحديداً. تجنّب إعادة استخدام نص خدمة أخرى بتبديل الكلمات — الصفحات المتشابهة تضرّ بترتيب بعضها." />
            </x-admin.panel>

            <x-admin.panel title="لمن هذه الخدمة"
                           description="من هو العميل المناسب لهذه الخدمة تحديداً. يظهر كقائمة في الصفحة.">
                <x-admin.repeater name="audience" label="الفئات المستهدفة" :rows="$service->audience ?? []" simple />
            </x-admin.panel>

            <x-admin.panel title="نطاق العمل"
                           description="ما الذي يشمله العمل فعلياً. تُستخدم هذه القائمة أيضاً في البيانات المنظمة للصفحة.">
                <x-admin.repeater name="scope" label="بنود نطاق العمل" :rows="$service->scope ?? []" simple />
            </x-admin.panel>

            <x-admin.panel title="مراحل العمل"
                           description="ما الذي يحدث عملياً بعد التواصل، مرحلة بمرحلة.">
                <x-admin.repeater name="process" label="المراحل" :rows="$service->process ?? []"
                                  :fields="['title' => 'عنوان المرحلة', 'body' => 'الشرح']" />
            </x-admin.panel>

            <x-admin.panel title="الأسئلة المتكررة"
                           description="من 3 إلى 5 أسئلة حقيقية يطرحها العملاء، بإجابات مفيدة فعلاً. لا تُضاف بيانات FAQ منظمة — هذا النوع من البيانات مقيّد لدى جوجل ولا يمنح نتيجة إضافية لهذا القطاع.">
                <x-admin.repeater name="faqs" label="الأسئلة" :rows="$service->faqs ?? []"
                                  :fields="['question' => 'السؤال', 'answer' => 'الإجابة']" />
            </x-admin.panel>

            <x-admin.panel title="إخلاء المسؤولية">
                <x-form.field name="disclaimer" label="نص التنبيه القانوني" type="textarea" rows="3"
                              :value="$service->disclaimer"
                              help="يظهر أسفل نص الخدمة. اتركه فارغاً إن لم يكن مناسباً." />
            </x-admin.panel>
        </div>

        {{-- Sidebar ------------------------------------------------------ --}}
        <div class="space-y-6">

            <x-admin.panel title="النشر">
                <label class="block">
                    <span class="block font-display text-sm text-ink">الحالة</span>
                    <select name="status" class="mt-2 w-full rounded-sm border border-stone bg-ivory px-4 py-3 text-sm">
                        <option value="draft" @selected(old('status', $service->status) === 'draft')>مسودة</option>
                        <option value="published" @selected(old('status', $service->status) === 'published')>منشور</option>
                    </select>
                    <span class="mt-1.5 block text-xs text-charcoal-soft">
                        المسودة غير ظاهرة للزوار وغير مدرجة في خريطة الموقع.
                    </span>
                </label>

                <x-form.field name="published_at" label="تاريخ النشر" type="datetime-local"
                              :value="$service->published_at?->format('Y-m-d\TH:i')" dir="ltr" />

                <label class="block">
                    <span class="block font-display text-sm text-ink">مجال الخدمة</span>
                    <select name="service_category_id" required
                            class="mt-2 w-full rounded-sm border border-stone bg-ivory px-4 py-3 text-sm">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                @selected(old('service_category_id', $service->service_category_id) == $category->id)>
                                {{ $category->title }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <x-form.field name="position" label="الترتيب" type="number" :value="$service->position ?? 0"
                              help="الأصغر يظهر أولاً." dir="ltr" />

                <label class="flex items-start gap-2.5 text-sm">
                    <input type="checkbox" name="needs_review" value="1" class="mt-1 size-4 rounded-sm border-stone"
                           @checked(old('needs_review', $service->needs_review))>
                    <span>
                        <span class="block text-ink">يحتاج مراجعة قانونية</span>
                        <span class="block text-xs text-charcoal-soft">تنبيه داخلي فقط، لا يظهر للزوار.</span>
                    </span>
                </label>

                <div class="flex gap-3 border-t border-stone pt-5">
                    <button type="submit"
                            class="flex-1 rounded-sm bg-ink px-5 py-3 font-display text-sm text-ivory hover:bg-ink-700">
                        {{ $isNew ? 'إنشاء' : 'حفظ' }}
                    </button>
                    <a href="{{ route('admin.services.index') }}"
                       class="rounded-sm border border-stone px-5 py-3 text-sm text-charcoal hover:bg-stone-soft">
                        إلغاء
                    </a>
                </div>
            </x-admin.panel>

            <x-admin.panel title="التراخيص ذات العلاقة"
                           description="اختر فقط التراخيص المرتبطة فعلياً بهذه الخدمة. تظهر في الصفحة كما هي دون أي إضافة.">
                @foreach ($licenses as $license)
                    <label class="flex items-start gap-2.5 text-sm">
                        <input type="checkbox" name="license_keys[]" value="{{ $license['key'] }}"
                               class="mt-1 size-4 rounded-sm border-stone"
                               @checked(in_array($license['key'], old('license_keys', $service->license_keys ?? []), true))>
                        <span>
                            <span class="block text-ink">{{ $license['label'] }}</span>
                            <span class="block text-xs text-charcoal-soft" dir="ltr" style="text-align: start;">
                                {{ $license['number'] }}
                            </span>
                        </span>
                    </label>
                @endforeach
            </x-admin.panel>

            <x-admin.panel title="تحسين الظهور (SEO)">
                <x-form.field name="seo_title" label="عنوان صفحة النتائج" :value="$service->seo_title"
                              help="حتى 70 حرفاً. يُستخدم العنوان الرئيسي إن تُرك فارغاً." />

                <x-form.field name="seo_description" label="وصف صفحة النتائج" type="textarea" rows="3"
                              :value="$service->seo_description"
                              help="حتى 160 حرفاً. اجعله وصفاً حقيقياً لما تقدّمه الصفحة." />

                <x-form.field name="focus_phrase" label="العبارة المستهدفة" :value="$service->focus_phrase"
                              help="لتنظيمك الداخلي فقط. لا تُحشر في العناوين والفقرات." />

                <x-form.field name="canonical_url" label="الرابط القياسي (canonical)" :value="$service->canonical_url"
                              dir="ltr" help="اتركه فارغاً — يُبنى تلقائياً. لا تعدّله إلا لتوجيه الصفحة إلى صفحة أخرى عمداً." />

                <label class="flex items-start gap-2.5 text-sm">
                    <input type="checkbox" name="is_indexable" value="1" class="mt-1 size-4 rounded-sm border-stone"
                           @checked(old('is_indexable', $service->is_indexable ?? true))>
                    <span>
                        <span class="block text-ink">السماح بفهرسة الصفحة</span>
                        <span class="block text-xs text-charcoal-soft">
                            عند الإلغاء تُضاف noindex وتُستبعد من خريطة الموقع.
                        </span>
                    </span>
                </label>
            </x-admin.panel>

            <x-admin.panel title="الروابط الداخلية"
                           description="الربط بين الصفحات ذات الصلة يساعد الزائر ومحرك البحث معاً.">
                <label class="block">
                    <span class="block font-display text-sm text-ink">خدمات ذات صلة</span>
                    <select name="related_service_ids[]" multiple size="8"
                            class="mt-2 w-full rounded-sm border border-stone bg-ivory px-3 py-2 text-sm">
                        @foreach ($allServices as $option)
                            @continue($option->id === $service->id)
                            <option value="{{ $option->id }}"
                                @selected(in_array($option->id, old('related_service_ids', $service->relatedServices->pluck('id')->all()), true))>
                                {{ $option->title }}
                            </option>
                        @endforeach
                    </select>
                </label>

                @if ($allPosts->isNotEmpty())
                    <label class="block">
                        <span class="block font-display text-sm text-ink">مقالات ذات صلة</span>
                        <select name="post_ids[]" multiple size="6"
                                class="mt-2 w-full rounded-sm border border-stone bg-ivory px-3 py-2 text-sm">
                            @foreach ($allPosts as $option)
                                <option value="{{ $option->id }}"
                                    @selected(in_array($option->id, old('post_ids', $service->posts->pluck('id')->all()), true))>
                                    {{ $option->title }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                @endif
            </x-admin.panel>
        </div>
    </div>
</form>

</x-layouts.admin>
