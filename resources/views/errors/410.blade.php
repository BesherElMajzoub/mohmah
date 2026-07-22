@php
    use App\Support\Seo\SeoData;

    $seo = new SeoData(
        title: 'صفحة لم تعد متاحة',
        indexable: false,
    );
@endphp

<x-layouts.public :seo="$seo">

{{-- 410 rather than 404: this URL existed and was intentionally retired.
     The distinction matters to crawlers — a 410 tells Google to drop the URL
     rather than keep retrying it for months. Served from the redirects table
     for legacy paths that have no meaningful successor. --}}

<div class="mx-auto flex min-h-[60vh] max-w-3xl flex-col items-center justify-center px-5 py-24 text-center lg:px-8">
    <span class="block h-px w-12 bg-gold" aria-hidden="true"></span>

    <h1 class="mt-8 font-display text-3xl text-ink md:text-4xl">هذه الصفحة لم تعد متاحة</h1>

    <p class="mt-5 max-w-lg leading-relaxed text-charcoal-soft">
        أُزيلت هذه الصفحة نهائياً ولا يوجد لها بديل مباشر. يمكنك تصفح خدمات المكتب
        الحالية أو التواصل معه.
    </p>

    <div class="mt-10 flex flex-col gap-3 sm:flex-row">
        <a href="{{ route('services.index') }}"
           class="inline-flex items-center justify-center rounded-sm border border-gold px-7 py-3.5
                  font-display text-ink transition-colors hover:bg-ink hover:text-ivory">
            تصفح الخدمات
        </a>
        <x-cta.call placement="error_410" label="اتصل بالمكتب" />
    </div>
</div>

</x-layouts.public>
