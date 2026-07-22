<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\ClickTrackingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
|
| Slugs are raw UTF-8 Arabic. Laravel matches them natively; URL *generation*
| goes through App\Support\Url so every emitted link is percent-encoded
| identically.
|
| The route file is deliberately shaped so a locale prefix could be added
| later without touching the controllers — but no English route, no locale
| segment and no hreflang ships until real English content exists.
|
*/

Route::get('/', HomeController::class)->name('home');

// --- Fixed pages ---------------------------------------------------------
// Each binds to a Page row by its immutable `key`, so the client can rewrite
// the copy freely without any risk of breaking the route.
Route::get('/عن-المحامي-ريان-الجهني', [PageController::class, 'about'])->name('about');
Route::get('/التراخيص-والاعتمادات', [PageController::class, 'licenses'])->name('licenses');
Route::get('/منهجية-العمل', [PageController::class, 'methodology'])->name('methodology');
Route::get('/سياسة-الخصوصية', [PageController::class, 'privacy'])->name('privacy');
Route::get('/الشروط-والأحكام', [PageController::class, 'terms'])->name('terms');

// --- Services ------------------------------------------------------------
// Binding is declared per-route as `{model:slug}` rather than via
// getRouteKeyName() on the models. The models are shared with the admin,
// where records are addressed by id — a global slug key would break every
// admin edit URL.
Route::get('/الخدمات', [ServiceController::class, 'index'])->name('services.index');
Route::get('/خدمات/{service:slug}', [ServiceController::class, 'show'])->name('services.show');

// --- Blog ----------------------------------------------------------------
// Categories and articles share the /المدونة/{slug} shape, so they cannot be
// two routes: implicit model binding aborts with a 404 when the first route's
// model is not found rather than falling through to the second, which would
// make every article URL unreachable behind the category route.
//
// One route dispatches on what the slug actually resolves to.
Route::get('/المدونة', [PostController::class, 'index'])->name('posts.index');
Route::get('/المدونة/{slug}', [PostController::class, 'entry'])->name('posts.entry');

// --- Contact -------------------------------------------------------------
Route::get('/تواصل-معنا', [ContactController::class, 'show'])->name('contact');
Route::post('/تواصل-معنا', [ContactController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('contact.store');

/*
|--------------------------------------------------------------------------
| Conversion tracking
|--------------------------------------------------------------------------
|
| Hit by navigator.sendBeacon. Never in the way of a conversion: the call and
| WhatsApp anchors are real links that work with this route unavailable.
|
*/

Route::post('/t/click', ClickTrackingController::class)
    ->middleware('throttle:30,1')
    ->name('track.click');

/*
|--------------------------------------------------------------------------
| Crawler files
|--------------------------------------------------------------------------
*/

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
|
| Kept entirely separate from the public site: its own prefix, its own layout
| and its own middleware. Nothing here is ever linked from a public page or
| listed in the sitemap.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [Admin\AuthController::class, 'show'])->name('login');
    Route::post('/login', [Admin\AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/logout', [Admin\AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::resource('services', Admin\ServiceController::class)->except('show');
        Route::resource('service-categories', Admin\ServiceCategoryController::class)->except('show');
        Route::resource('posts', Admin\PostController::class)->except('show');
        Route::resource('post-categories', Admin\PostCategoryController::class)->except('show');
        Route::resource('pages', Admin\PageController::class)->only(['index', 'edit', 'update']);
        Route::resource('redirects', Admin\RedirectController::class)->except('show');

        // Media is upload + edit metadata + delete; there is no "create form".
        Route::get('media', [Admin\MediaController::class, 'index'])->name('media.index');
        Route::post('media', [Admin\MediaController::class, 'store'])->name('media.store');
        Route::put('media/{medium}', [Admin\MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{medium}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');

        Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');

        Route::get('clicks', [Admin\ClickAnalyticsController::class, 'index'])->name('clicks.index');
        Route::get('clicks/export', [Admin\ClickAnalyticsController::class, 'export'])->name('clicks.export');

        Route::get('submissions', [Admin\SubmissionController::class, 'index'])->name('submissions.index');
        Route::delete('submissions/{submission}', [Admin\SubmissionController::class, 'destroy'])->name('submissions.destroy');
    });
