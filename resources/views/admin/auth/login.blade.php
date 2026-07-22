<!doctype html>
<html lang="ar-SA" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>تسجيل الدخول — لوحة التحكم</title>
    <link rel="icon" href="{{ asset('brand/favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-ink px-5 on-dark">

<div class="grid-motif absolute inset-0 opacity-40" aria-hidden="true"></div>

<main class="relative w-full max-w-sm">
    <div class="text-center">
        <img src="{{ asset('brand/logo-mark.png') }}" alt="" width="121" height="240" class="mx-auto h-16 w-auto">
        <h1 class="mt-6 font-display text-xl text-ivory">لوحة التحكم</h1>
        <p class="mt-2 text-sm text-ivory/50">{{ config('site.name') }}</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5 rounded-sm bg-ivory p-7">
        @csrf

        <x-form.field name="email" label="البريد الإلكتروني" type="email" dir="ltr" required autofocus autocomplete="username" />
        <x-form.field name="password" label="كلمة المرور" type="password" dir="ltr" required autocomplete="current-password" />

        <label class="flex items-center gap-2.5 text-sm text-charcoal">
            <input type="checkbox" name="remember" value="1" class="size-4 rounded-sm border-stone text-ink">
            تذكّرني على هذا الجهاز
        </label>

        <button type="submit"
                class="w-full rounded-sm bg-ink px-7 py-3.5 font-display text-ivory transition-colors hover:bg-ink-700">
            تسجيل الدخول
        </button>
    </form>

    <p class="mt-6 text-center text-sm">
        <a href="{{ route('home') }}" class="text-ivory/50 underline-offset-4 hover:text-gold-soft hover:underline">
            العودة إلى الموقع
        </a>
    </p>
</main>

</body>
</html>
