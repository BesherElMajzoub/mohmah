<?php

use App\Models\Post;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
*/

// Yesterday's conversion summary, sent early morning Riyadh time so it is
// waiting when the office opens. No-ops unless enabled in settings.
Schedule::command('clicks:digest')
    ->dailyAt('07:00')
    ->timezone('Asia/Riyadh');

// A scheduled article goes live the moment its date passes — scopePublished()
// decides that from the clock, so this changes nothing a visitor sees. It
// only flips the stored status so the admin list stops labelling a live
// article "مجدول".
Schedule::call(function () {
    Post::query()
        ->where('status', Post::STATUS_SCHEDULED)
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->each(fn (Post $post) => $post->update(['status' => Post::STATUS_PUBLISHED]));
})->everyFifteenMinutes()->name('publish-scheduled-posts');
