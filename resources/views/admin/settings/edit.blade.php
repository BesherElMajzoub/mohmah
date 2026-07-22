@php
    $social = (array) ($values['social_links'] ?? []);
    // Normalise the stored label=>url map into indexed rows for the repeater.
    $socialRows = [];
    foreach ($social as $label => $url) {
        $socialRows[] = ['label' => $label, 'url' => $url];
    }
@endphp

<x-layouts.admin title="إعدادات الموقع">

<form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-3xl space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-sm border-s-4 border-gold bg-ivory p-5 text-sm leading-relaxed text-charcoal">
        <p class="font-display text-ink">الحقول الفارغة لا تظهر في الموقع</p>
        <p class="mt-2">
            أي حقل تتركه فارغاً هنا يختفي قسمه بالكامل من الموقع — لا يُعرض عنوان
            تقريبي ولا أوقات عمل مفترضة ولا حساب تواصل غير موجود. اتركه فارغاً حتى
            تتوفر المعلومة الصحيحة.
        </p>
    </div>

    <x-admin.panel title="بيانات التواصل">
        <div class="rounded-sm bg-stone-soft/60 p-4 text-sm">
            <p class="text-charcoal-soft">رقم الهاتف والواتساب مضبوطان في إعدادات النظام:</p>
            <p class="mt-1.5 font-display text-ink num">{{ config('site.phone_display') }}</p>
        </div>

        <x-form.field name="office_address" label="عنوان المكتب" type="textarea" rows="3"
                      :value="$values['office_address'] ?? null"
                      help="يظهر في التذييل وصفحة التواصل، ويُدرج في البيانات المنظمة." />

        <x-form.field name="map_url" label="رابط خرائط جوجل" :value="$values['map_url'] ?? null" dir="ltr"
                      help="الرابط الكامل لموقع المكتب على الخريطة." />

        <x-form.field name="office_hours" label="أوقات العمل" type="textarea" rows="2"
                      :value="$values['office_hours'] ?? null"
                      help="بصيغة يقرأها الزائر، مثل: الأحد إلى الخميس، 9 صباحاً — 5 مساءً." />

        <x-form.field name="office_hours_schema" label="أوقات العمل بصيغة البيانات المنظمة"
                      :value="$values['office_hours_schema'] ?? null" dir="ltr"
                      help="صيغة تقنية لمحركات البحث، مثل: Su-Th 09:00-17:00. اتركها فارغة إن لم تكن متأكداً." />

        <x-form.field name="office_email" label="البريد الإلكتروني للمكتب" type="email"
                      :value="$values['office_email'] ?? null" dir="ltr" />
    </x-admin.panel>

    <x-admin.panel title="حسابات التواصل"
                   description="أضف الحسابات الرسمية المعتمدة فقط. الصفوف غير المكتملة تُتجاهل، ولا تُعرض أي أيقونة لحساب غير موجود.">
        <x-admin.repeater name="social" label="الحسابات" :rows="$socialRows"
                          :fields="['label' => 'اسم المنصة', 'url' => 'رابط الحساب']" />
    </x-admin.panel>

    <x-admin.panel title="نموذج التواصل">
        <label class="flex items-start gap-2.5 text-sm">
            <input type="checkbox" name="contact_form_enabled" value="1" class="mt-1 size-4 rounded-sm border-stone"
                   @checked($values['contact_form_enabled'] ?? false)>
            <span>
                <span class="block text-ink">تفعيل نموذج التواصل</span>
                <span class="block text-xs leading-relaxed text-charcoal-soft">
                    النموذج إضافي فقط. يبقى الهاتف والواتساب متاحين بنقرة واحدة في كل الأحوال،
                    ولا يُطلب من الزائر تعبئة نموذج قبل أن يتمكن من الاتصال.
                </span>
            </span>
        </label>
    </x-admin.panel>

    <x-admin.panel title="تنبيهات التحويلات"
                   description="إشعارات بالبريد عند وصول نقرة اتصال أو واتساب.">
        <x-form.field name="alerts_email" label="بريد استقبال التنبيهات" type="email"
                      :value="$values['alerts_email'] ?? null" dir="ltr" />

        <label class="flex items-start gap-2.5 text-sm">
            <input type="checkbox" name="alerts_digest_enabled" value="1" class="mt-1 size-4 rounded-sm border-stone"
                   @checked($values['alerts_digest_enabled'] ?? false)>
            <span>
                <span class="block text-ink">ملخص يومي</span>
                <span class="block text-xs text-charcoal-soft">
                    رسالة واحدة كل صباح بملخص نقرات اليوم السابق. لا تُرسل رسالة في الأيام التي لا نقرات فيها.
                </span>
            </span>
        </label>

        <label class="flex items-start gap-2.5 text-sm">
            <input type="checkbox" name="alerts_instant_enabled" value="1" class="mt-1 size-4 rounded-sm border-stone"
                   @checked($values['alerts_instant_enabled'] ?? false)>
            <span>
                <span class="block text-ink">تنبيه فوري لكل نقرة</span>
                <span class="block text-xs text-charcoal-soft">
                    مناسب للمكاتب التي تريد معرفة كل تواصل فور حدوثه. قد يعني رسائل كثيرة في أوقات الحملات الإعلانية.
                </span>
            </span>
        </label>
    </x-admin.panel>

    <x-admin.panel title="أدوات القياس">
        <p class="text-sm leading-relaxed text-charcoal-soft">
            معرّفات Google Analytics و Google Ads تُضبط في ملف البيئة
            (<span dir="ltr" class="font-mono text-xs">.env</span>) لا من هنا، لأنها تختلف بين
            بيئة التطوير والموقع المنشور.
        </p>

        <dl class="space-y-2 rounded-sm bg-stone-soft/60 p-4 text-sm">
            <div class="flex items-baseline justify-between gap-4">
                <dt class="text-charcoal-soft" dir="ltr">SITE_GA4_ID</dt>
                <dd class="font-display">{{ config('site.ga4_id') ?: 'غير مضبوط' }}</dd>
            </div>
            <div class="flex items-baseline justify-between gap-4">
                <dt class="text-charcoal-soft" dir="ltr">SITE_GOOGLE_ADS_ID</dt>
                <dd class="font-display">{{ config('site.google_ads_id') ?: 'غير مضبوط' }}</dd>
            </div>
            <div class="flex items-baseline justify-between gap-4">
                <dt class="text-charcoal-soft" dir="ltr">SITE_INDEXABLE</dt>
                <dd class="font-display">{{ config('site.indexable') ? 'مفعّلة' : 'معطّلة (لا فهرسة)' }}</dd>
            </div>
        </dl>

        <p class="text-xs leading-relaxed text-charcoal-soft">
            ما دامت المعرّفات فارغة لا يُحمَّل أي كود تتبع خارجي على الموقع إطلاقاً.
            تتبع النقرات الداخلي في لوحة التحكم يعمل بشكل مستقل عنها.
        </p>
    </x-admin.panel>

    <div class="flex gap-3">
        <button type="submit" class="rounded-sm bg-ink px-7 py-3.5 font-display text-sm text-ivory hover:bg-ink-700">
            حفظ الإعدادات
        </button>
    </div>
</form>

</x-layouts.admin>
