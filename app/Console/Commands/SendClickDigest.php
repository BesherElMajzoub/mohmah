<?php

namespace App\Console\Commands;

use App\Models\ClickEvent;
use App\Notifications\ClickDigest;
use App\Support\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Emails yesterday's conversion summary.
 *
 * Scheduled in routes/console.php. Silently does nothing unless the client
 * has entered an alerts address and enabled the digest, so a fresh install
 * never mails anyone.
 */
class SendClickDigest extends Command
{
    protected $signature = 'clicks:digest';

    protected $description = 'إرسال ملخص يومي بنقرات الاتصال والواتساب';

    public function handle(Settings $settings): int
    {
        if (! $settings->get('alerts_digest_enabled', false)) {
            $this->info('التقرير اليومي معطّل في الإعدادات.');

            return self::SUCCESS;
        }

        $recipient = $settings->get('alerts_email');

        if (! $recipient) {
            $this->warn('لم يتم ضبط بريد التنبيهات.');

            return self::SUCCESS;
        }

        $from = now()->subDay()->startOfDay();
        $to = now()->subDay()->endOfDay();

        $events = ClickEvent::query()->between($from, $to)->get();

        // No clicks means no email. A daily "zero" message trains the reader
        // to ignore the whole channel.
        if ($events->isEmpty()) {
            $this->info('لا توجد نقرات في الفترة المحددة.');

            return self::SUCCESS;
        }

        Notification::route('mail', $recipient)
            ->notify(new ClickDigest($events, $from));

        $this->info("تم إرسال الملخص إلى {$recipient} ({$events->count()} نقرة).");

        return self::SUCCESS;
    }
}
