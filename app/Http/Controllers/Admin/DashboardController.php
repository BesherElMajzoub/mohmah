<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClickEvent;
use App\Models\ContactSubmission;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Support\Content;
use App\Support\Settings;
use Illuminate\View\View;

/**
 * The admin landing screen.
 *
 * Built around what the office actually needs to see: how many enquiries came
 * in, and what still blocks the site from being complete. The "outstanding"
 * list is the mechanism that keeps [[NEEDS_CLIENT_CONFIRMATION]] visible to
 * the client instead of quietly living in the database forever.
 */
class DashboardController extends Controller
{
    public function __invoke(Settings $settings): View
    {
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        return view('admin.dashboard', [
            'clicksToday' => ClickEvent::query()->where('created_at', '>=', $today)->count(),
            'clicksMonth' => ClickEvent::query()->where('created_at', '>=', $monthStart)->count(),
            'callsMonth' => ClickEvent::query()->where('created_at', '>=', $monthStart)->where('type', ClickEvent::TYPE_CALL)->count(),
            'whatsappMonth' => ClickEvent::query()->where('created_at', '>=', $monthStart)->where('type', ClickEvent::TYPE_WHATSAPP)->count(),

            'servicesPublished' => Service::query()->published()->count(),
            'servicesDraft' => Service::query()->where('status', Service::STATUS_DRAFT)->count(),
            'postsPublished' => Post::query()->published()->count(),
            'postsDraft' => Post::query()->where('status', Post::STATUS_DRAFT)->count(),

            'unreadSubmissions' => ContactSubmission::query()->whereNull('read_at')->count(),

            'outstanding' => $this->outstanding($settings),
        ]);
    }

    /**
     * What the client still needs to supply or approve.
     *
     * @return array<int, array{label: string, done: bool, hint: string}>
     */
    private function outstanding(Settings $settings): array
    {
        $items = [
            [
                'label' => 'عنوان المكتب',
                'done' => $settings->filled('office_address'),
                'hint' => 'لا يظهر أي عنوان في الموقع حتى تتم إضافته — لا يُعرض عنوان تقريبي.',
            ],
            [
                'label' => 'رابط خرائط جوجل',
                'done' => $settings->filled('map_url'),
                'hint' => 'يظهر زر «عرض الموقع على الخريطة» في التذييل بعد إضافته.',
            ],
            [
                'label' => 'أوقات العمل',
                'done' => $settings->filled('office_hours'),
                'hint' => 'تُعرض في التذييل وصفحة التواصل، وتُدرج في البيانات المنظمة.',
            ],
            [
                'label' => 'البريد الإلكتروني للمكتب',
                'done' => $settings->filled('office_email'),
                'hint' => 'يظهر في التذييل وصفحة التواصل بعد إضافته.',
            ],
            [
                'label' => 'حسابات التواصل المعتمدة',
                'done' => $settings->filled('social_links'),
                'hint' => 'لا تُعرض أيقونات أو روابط فارغة — تُضاف الحسابات الحقيقية فقط.',
            ],
            [
                'label' => 'معرّف Google Analytics 4',
                'done' => filled(config('site.ga4_id')),
                'hint' => 'يُضبط في ملف البيئة‏ SITE_GA4_ID. لا يُحمَّل أي كود تتبع قبل ذلك.',
            ],
            [
                'label' => 'السيرة المهنية للمحامي',
                'done' => ! $this->pageNeedsConfirmation(Page::KEY_ABOUT),
                'hint' => 'صفحة «عن المحامي» تحتوي على تنبيه داخلي بانتظار اعتماد السيرة.',
            ],
            [
                'label' => 'مراجعة نصوص الخدمات قانونياً',
                'done' => Service::query()->where('needs_review', true)->doesntExist(),
                'hint' => 'النصوص مكتوبة من نطاق الخدمة المزوّد وتحتاج مراجعة قبل اعتمادها نهائياً.',
            ],
        ];

        // Outstanding items first — the list exists to be acted on.
        usort($items, fn ($a, $b) => $a['done'] <=> $b['done']);

        return $items;
    }

    private function pageNeedsConfirmation(string $key): bool
    {
        $page = Page::query()->where('key', $key)->first();

        return $page !== null && Content::needsConfirmation($page->body);
    }
}
