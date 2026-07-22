@php
    use App\Support\Seo\SeoData;

    // Error pages are always noindex: a 404 that is indexable can end up in
    // search results as a real page.
    $seo = new SeoData(
        title: 'الصفحة غير موجودة',
        description: null,
        indexable: false,
    );
@endphp

<x-layouts.public :seo="$seo">

<div class="mx-auto flex min-h-[60vh] max-w-3xl flex-col items-center justify-center px-5 py-24 text-center lg:px-8">
    <span class="block h-px w-12 bg-gold" aria-hidden="true"></span>

    <p class="mt-8 font-display text-5xl text-gold-deep num">404</p>

    <h1 class="mt-6 font-display text-3xl text-ink md:text-4xl">الصفحة غير موجودة</h1>

    <p class="mt-5 max-w-lg leading-relaxed text-charcoal-soft">
        قد يكون الرابط قديماً أو غير صحيح. يمكنك تصفح الخدمات، أو التواصل مع المكتب
        مباشرة لعرض احتياجك.
    </p>

    <div class="mt-10 flex flex-col gap-3 sm:flex-row">
        <a href="{{ route('services.index') }}"
           class="inline-flex items-center justify-center rounded-sm border border-gold px-7 py-3.5
                  font-display text-ink transition-colors hover:bg-ink hover:text-ivory">
            تصفح الخدمات
        </a>
        <x-cta.call placement="error_404" label="اتصل بالمكتب" />
    </div>

    <p class="mt-10">
        <a href="{{ route('home') }}" class="text-sm text-charcoal-soft underline underline-offset-4 hover:text-ink">
            العودة إلى الصفحة الرئيسية
        </a>
    </p>
</div>

</x-layouts.public>
