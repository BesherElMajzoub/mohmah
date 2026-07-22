@php
    use App\Models\ClickEvent;

    $max = max(1, ...array_values($daily ?: [1]));
    $query = ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'type' => $type];
@endphp

<x-layouts.admin title="تتبع النقرات">

<x-slot:actions>
    <a href="{{ route('admin.clicks.export', $query) }}"
       class="rounded-sm border border-stone px-5 py-2.5 text-sm text-charcoal hover:bg-stone-soft">
        تصدير CSV
    </a>
</x-slot:actions>

{{-- Filters ------------------------------------------------------------ --}}
<form method="GET" class="rounded-sm border border-stone bg-ivory p-5">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label for="from" class="block text-sm text-charcoal-soft">من تاريخ</label>
            <input type="date" id="from" name="from" value="{{ $from->toDateString() }}"
                   class="mt-1.5 w-full rounded-sm border border-stone bg-ivory px-3 py-2.5 text-sm">
        </div>
        <div>
            <label for="to" class="block text-sm text-charcoal-soft">إلى تاريخ</label>
            <input type="date" id="to" name="to" value="{{ $to->toDateString() }}"
                   class="mt-1.5 w-full rounded-sm border border-stone bg-ivory px-3 py-2.5 text-sm">
        </div>
        <div>
            <label for="type" class="block text-sm text-charcoal-soft">النوع</label>
            <select id="type" name="type" class="mt-1.5 w-full rounded-sm border border-stone bg-ivory px-3 py-2.5 text-sm">
                <option value="">الكل</option>
                @foreach (ClickEvent::TYPES as $t)
                    <option value="{{ $t }}" @selected($type === $t)>{{ ClickEvent::typeLabel($t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full rounded-sm bg-ink px-5 py-2.5 font-display text-sm text-ivory hover:bg-ink-700">
                تطبيق
            </button>
        </div>
    </div>
</form>

{{-- Totals -------------------------------------------------------------- --}}
<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-sm border border-stone bg-ivory p-6">
        <p class="text-sm text-charcoal-soft">إجمالي النقرات</p>
        <p class="mt-2 font-display text-3xl text-ink num">{{ number_format($total) }}</p>
    </div>

    @foreach (ClickEvent::TYPES as $t)
        <div class="rounded-sm border border-stone bg-ivory p-6">
            <p class="text-sm text-charcoal-soft">{{ ClickEvent::typeLabel($t) }}</p>
            <p class="mt-2 font-display text-3xl text-ink num">{{ number_format($byType[$t] ?? 0) }}</p>
        </div>
    @endforeach
</div>

{{-- Daily chart ---------------------------------------------------------
     Plain CSS bars rather than a charting library: the data is one series
     of daily counts, and shipping a chart bundle into the admin for that
     would be more code than the feature. --}}
<section class="mt-8 rounded-sm border border-stone bg-ivory p-6">
    <h2 class="font-display text-lg text-ink">النقرات اليومية</h2>

    @if ($total === 0)
        <p class="mt-6 text-sm text-charcoal-soft">لا توجد نقرات في هذه الفترة.</p>
    @else
        <div class="mt-6 flex h-48 items-end gap-px overflow-x-auto" role="img"
             aria-label="رسم بياني للنقرات اليومية خلال الفترة المحددة">
            @foreach ($daily as $day => $count)
                <div class="group relative flex min-w-[6px] flex-1 flex-col justify-end"
                     style="height: 100%;">
                    <div class="w-full rounded-t-sm bg-ink transition-colors group-hover:bg-gold"
                         style="height: {{ max(2, (int) round($count / $max * 100)) }}%;"></div>

                    {{-- Values are in a table below as well, so the chart is
                         decorative rather than the only way to read the data. --}}
                    <span class="pointer-events-none absolute -top-7 start-1/2 hidden -translate-x-1/2 whitespace-nowrap
                                 rounded-sm bg-ink px-2 py-1 text-xs text-ivory group-hover:block">
                        {{ $day }} — {{ $count }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="mt-3 flex justify-between text-xs text-charcoal-soft">
            <span>{{ $from->format('Y-m-d') }}</span>
            <span>{{ $to->format('Y-m-d') }}</span>
        </div>
    @endif
</section>

{{-- Breakdowns ---------------------------------------------------------- --}}
<div class="mt-8 grid gap-6 lg:grid-cols-2">

    <section class="rounded-sm border border-stone bg-ivory p-6">
        <h2 class="font-display text-lg text-ink">أكثر الصفحات تحويلاً</h2>
        @if ($byPage->isEmpty())
            <p class="mt-4 text-sm text-charcoal-soft">لا توجد بيانات.</p>
        @else
            <table class="mt-5 w-full text-sm">
                <caption class="sr-only">عدد النقرات لكل صفحة</caption>
                <thead>
                    <tr class="border-b border-stone text-start text-charcoal-soft">
                        <th scope="col" class="pb-2 text-start font-normal">الصفحة</th>
                        <th scope="col" class="pb-2 text-start font-normal">النقرات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone">
                    @foreach ($byPage as $row)
                        <tr>
                            <td class="py-2.5 pe-4">
                                <span class="block max-w-md truncate" dir="ltr" style="text-align: start;">
                                    {{ rawurldecode($row->page_path) }}
                                </span>
                            </td>
                            <td class="py-2.5 font-display num">{{ $row->total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <section class="rounded-sm border border-stone bg-ivory p-6">
        <h2 class="font-display text-lg text-ink">مصادر الزيارات</h2>
        @if ($bySource->isEmpty())
            <p class="mt-4 text-sm text-charcoal-soft">لا توجد بيانات.</p>
        @else
            <table class="mt-5 w-full text-sm">
                <caption class="sr-only">عدد النقرات لكل مصدر</caption>
                <thead>
                    <tr class="border-b border-stone text-charcoal-soft">
                        <th scope="col" class="pb-2 text-start font-normal">المصدر</th>
                        <th scope="col" class="pb-2 text-start font-normal">النقرات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone">
                    @foreach ($bySource as $source => $count)
                        <tr>
                            <td class="py-2.5 pe-4">{{ $source }}</td>
                            <td class="py-2.5 font-display num">{{ $count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <section class="rounded-sm border border-stone bg-ivory p-6">
        <h2 class="font-display text-lg text-ink">موضع الزر</h2>
        @if ($byPlacement->isEmpty())
            <p class="mt-4 text-sm text-charcoal-soft">لا توجد بيانات.</p>
        @else
            <ul class="mt-5 divide-y divide-stone text-sm">
                @foreach ($byPlacement as $row)
                    <li class="flex items-baseline justify-between gap-4 py-2.5">
                        <span dir="ltr" style="text-align: start;">{{ $row->placement ?? '—' }}</span>
                        <span class="font-display num">{{ $row->total }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="rounded-sm border border-stone bg-ivory p-6">
        <h2 class="font-display text-lg text-ink">الأجهزة</h2>
        @if ($byDevice->isEmpty())
            <p class="mt-4 text-sm text-charcoal-soft">لا توجد بيانات.</p>
        @else
            @php
                $deviceLabels = ['mobile' => 'جوال', 'tablet' => 'جهاز لوحي', 'desktop' => 'حاسب مكتبي'];
            @endphp
            <ul class="mt-5 divide-y divide-stone text-sm">
                @foreach ($byDevice as $row)
                    <li class="flex items-baseline justify-between gap-4 py-2.5">
                        <span>{{ $deviceLabels[$row->device] ?? ($row->device ?? '—') }}</span>
                        <span class="font-display num">{{ $row->total }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>

{{-- Recent events -------------------------------------------------------- --}}
<section class="mt-8 rounded-sm border border-stone bg-ivory p-6">
    <h2 class="font-display text-lg text-ink">أحدث النقرات</h2>

    @if ($recent->isEmpty())
        <p class="mt-4 text-sm text-charcoal-soft">لا توجد نقرات مسجّلة في هذه الفترة.</p>
    @else
        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[44rem] text-sm">
                <caption class="sr-only">قائمة بأحدث نقرات الاتصال والواتساب</caption>
                <thead>
                    <tr class="border-b border-stone text-charcoal-soft">
                        <th scope="col" class="pb-2 text-start font-normal">الوقت</th>
                        <th scope="col" class="pb-2 text-start font-normal">النوع</th>
                        <th scope="col" class="pb-2 text-start font-normal">الصفحة</th>
                        <th scope="col" class="pb-2 text-start font-normal">الموضع</th>
                        <th scope="col" class="pb-2 text-start font-normal">المصدر</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone">
                    @foreach ($recent as $event)
                        <tr>
                            <td class="whitespace-nowrap py-2.5 pe-4 num">
                                {{ $event->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                            </td>
                            <td class="py-2.5 pe-4">{{ ClickEvent::typeLabel($event->type) }}</td>
                            <td class="py-2.5 pe-4">
                                <span class="block max-w-xs truncate" dir="ltr" style="text-align: start;">
                                    {{ rawurldecode((string) $event->page_path) }}
                                </span>
                            </td>
                            <td class="py-2.5 pe-4" dir="ltr" style="text-align: start;">{{ $event->placement }}</td>
                            <td class="py-2.5">{{ $event->sourceLabel() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

<p class="mt-6 rounded-sm bg-stone-soft/60 p-4 text-xs leading-relaxed text-charcoal-soft">
    تُسجَّل هذه البيانات على خادم الموقع مباشرة، ولا تعتمد على أدوات خارجية قد
    يحجبها المتصفح. لا يُخزَّن عنوان IP للزائر، بل قيمة مشفّرة منه لا يمكن الرجوع
    منها إلى العنوان الأصلي.
</p>

</x-layouts.admin>
