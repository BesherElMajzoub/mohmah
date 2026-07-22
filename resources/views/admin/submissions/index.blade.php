<x-layouts.admin title="رسائل نموذج التواصل">

@if ($submissions->isEmpty())
    <p class="rounded-sm border border-stone bg-ivory p-10 text-center text-charcoal-soft">
        لا توجد رسائل.
    </p>
@else
    <div class="space-y-4">
        @foreach ($submissions as $submission)
            <article class="rounded-sm border border-stone bg-ivory p-6">
                <div class="flex flex-wrap items-baseline justify-between gap-4">
                    <div>
                        <p class="font-display text-lg text-ink">{{ $submission->name }}</p>
                        <p class="mt-1 text-sm text-charcoal-soft">
                            <a href="tel:{{ $submission->phone }}" class="hover:underline" dir="ltr">
                                {{ $submission->phone }}
                            </a>
                            @if ($submission->email)
                                <span aria-hidden="true">·</span>
                                <a href="mailto:{{ $submission->email }}" class="hover:underline" dir="ltr">
                                    {{ $submission->email }}
                                </a>
                            @endif
                        </p>
                    </div>

                    <time class="text-sm text-charcoal-soft num"
                          datetime="{{ $submission->created_at?->toIso8601String() }}">
                        {{ $submission->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                    </time>
                </div>

                @if ($submission->subject)
                    <p class="mt-4 font-display text-sm text-ink">{{ $submission->subject }}</p>
                @endif

                <p class="mt-3 whitespace-pre-line leading-relaxed text-charcoal">{{ $submission->message }}</p>

                @if ($submission->utm_source || $submission->gclid)
                    <p class="mt-4 text-xs text-charcoal-soft">
                        المصدر:
                        {{ $submission->gclid ? 'إعلانات جوجل' : $submission->utm_source }}
                        {{ $submission->utm_campaign ? '— '.$submission->utm_campaign : '' }}
                    </p>
                @endif

                <form method="POST" action="{{ route('admin.submissions.destroy', $submission) }}" class="mt-5"
                      onsubmit="return confirm('سيتم حذف الرسالة نهائياً. هل أنت متأكد؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-700 hover:underline">حذف الرسالة</button>
                </form>
            </article>
        @endforeach
    </div>

    <div class="mt-8">{{ $submissions->links() }}</div>
@endif

</x-layouts.admin>
