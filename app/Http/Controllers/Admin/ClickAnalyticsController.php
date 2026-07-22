<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClickEvent;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Conversion analytics.
 *
 * Answers four questions the office actually has: how many enquiries came in,
 * which pages produced them, where the visitors came from, and on what
 * device. Everything is aggregated in SQL rather than in PHP so the screen
 * stays fast as the table grows.
 */
class ClickAnalyticsController extends Controller
{
    private const MAX_RANGE_DAYS = 366;

    public function index(Request $request): View
    {
        [$from, $to] = $this->range($request);
        $type = $this->type($request);

        $base = fn (): Builder => ClickEvent::query()->between($from, $to)->ofType($type);

        return view('admin.clicks.index', [
            'from' => $from,
            'to' => $to,
            'type' => $type,

            'total' => $base()->count(),
            'byType' => ClickEvent::query()
                ->between($from, $to)
                ->selectRaw('type, COUNT(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type'),

            'daily' => $this->daily($from, $to, $type),

            'byPage' => $base()
                ->selectRaw('page_path, COUNT(*) as total')
                ->groupBy('page_path')
                ->orderByDesc('total')
                ->limit(15)
                ->get(),

            'byPlacement' => $base()
                ->selectRaw('placement, COUNT(*) as total')
                ->groupBy('placement')
                ->orderByDesc('total')
                ->get(),

            'byDevice' => $base()
                ->selectRaw('device, COUNT(*) as total')
                ->groupBy('device')
                ->orderByDesc('total')
                ->get(),

            'bySource' => $this->bySource($from, $to, $type),

            'recent' => $base()->latest()->limit(50)->get(),
        ]);
    }

    /**
     * A row per day across the whole range, including days with no clicks.
     *
     * Grouping in SQL alone would omit empty days, and a chart that silently
     * skips them misrepresents a quiet week as a continuous line.
     *
     * @return array<string, int>
     */
    private function daily(CarbonImmutable $from, CarbonImmutable $to, ?string $type): array
    {
        $counts = ClickEvent::query()
            ->between($from, $to)
            ->ofType($type)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $series = [];

        for ($day = $from->startOfDay(); $day->lte($to); $day = $day->addDay()) {
            $key = $day->format('Y-m-d');
            $series[$key] = (int) ($counts[$key] ?? 0);
        }

        return $series;
    }

    /**
     * Traffic sources, collapsed into labels a non-technical reader can use.
     *
     * @return Collection<string, int>
     */
    private function bySource(CarbonImmutable $from, CarbonImmutable $to, ?string $type)
    {
        return ClickEvent::query()
            ->between($from, $to)
            ->ofType($type)
            ->get(['gclid', 'utm_source', 'utm_campaign', 'referrer'])
            ->groupBy(fn (ClickEvent $e) => $e->sourceLabel())
            ->map->count()
            ->sortDesc();
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $type = $this->type($request);

        $filename = 'clicks-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($from, $to, $type) {
            $handle = fopen('php://output', 'wb');

            // UTF-8 BOM: without it Excel on Windows renders the Arabic
            // column values as mojibake.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'التاريخ', 'النوع', 'الصفحة', 'الموضع', 'الجهاز',
                'المصدر', 'الحملة', 'gclid', 'صفحة الوصول', 'الإحالة',
            ]);

            // Chunked so an export of a large range does not load the whole
            // table into memory.
            ClickEvent::query()
                ->between($from, $to)
                ->ofType($type)
                ->orderBy('id')
                ->chunk(500, function ($events) use ($handle) {
                    foreach ($events as $event) {
                        fputcsv($handle, [
                            $event->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i'),
                            ClickEvent::typeLabel($event->type),
                            $event->page_path,
                            $event->placement,
                            $event->device,
                            $event->utm_source,
                            $event->utm_campaign,
                            $event->gclid,
                            $event->landing_path,
                            $event->referrer,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function range(Request $request): array
    {
        $to = rescue(
            fn () => CarbonImmutable::parse($request->string('to')->value())->endOfDay(),
            CarbonImmutable::now()->endOfDay(),
            report: false,
        );

        $from = rescue(
            fn () => CarbonImmutable::parse($request->string('from')->value())->startOfDay(),
            $to->subDays(29)->startOfDay(),
            report: false,
        );

        // Guard against a reversed or absurd range arriving from the query
        // string, which would otherwise produce an enormous daily series.
        if ($from->gt($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        if ($from->diffInDays($to) > self::MAX_RANGE_DAYS) {
            $from = $to->subDays(self::MAX_RANGE_DAYS)->startOfDay();
        }

        return [$from, $to];
    }

    private function type(Request $request): ?string
    {
        $type = $request->string('type')->value();

        return in_array($type, ClickEvent::TYPES, true) ? $type : null;
    }
}
