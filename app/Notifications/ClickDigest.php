<?php

namespace App\Notifications;

use App\Models\ClickEvent;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * Daily conversion summary: how many enquiries, of what kind, from where.
 */
class ClickDigest extends Notification
{
    use Queueable;

    /**
     * @param  Collection<int, ClickEvent>  $events
     */
    public function __construct(
        private readonly Collection $events,
        private readonly CarbonInterface $date,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $day = $this->date->format('Y-m-d');

        $mail = (new MailMessage)
            ->subject("ملخص التحويلات ليوم {$day}")
            ->greeting("ملخص التحويلات — {$day}")
            ->line('إجمالي النقرات: '.$this->events->count());

        foreach ($this->events->groupBy('type') as $type => $group) {
            $mail->line(ClickEvent::typeLabel((string) $type).': '.$group->count());
        }

        // The three pages that produced the most enquiries — the actionable
        // part of the summary.
        $topPages = $this->events
            ->groupBy('page_path')
            ->map->count()
            ->sortDesc()
            ->take(3);

        if ($topPages->isNotEmpty()) {
            $mail->line('---');
            $mail->line('أكثر الصفحات تحويلاً:');

            foreach ($topPages as $path => $count) {
                $mail->line("{$path} — {$count}");
            }
        }

        $topSources = $this->events
            ->groupBy(fn (ClickEvent $e) => $e->sourceLabel())
            ->map->count()
            ->sortDesc()
            ->take(3);

        if ($topSources->isNotEmpty()) {
            $mail->line('---');
            $mail->line('أبرز المصادر:');

            foreach ($topSources as $source => $count) {
                $mail->line("{$source} — {$count}");
            }
        }

        return $mail->salutation('مكتب المحامي ريان الجهني');
    }
}
