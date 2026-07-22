<?php

namespace App\Notifications;

use App\Models\ClickEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Instant notification that someone tapped call or WhatsApp.
 *
 * Queued so the visitor's beacon request is never waiting on an SMTP round
 * trip.
 */
class ClickAlert extends Notification
{
    use Queueable;

    public function __construct(private readonly ClickEvent $event) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = ClickEvent::typeLabel($this->event->type);

        return (new MailMessage)
            ->subject("تنبيه: {$type} جديد من الموقع")
            ->greeting('تنبيه تحويل جديد')
            ->line("النوع: {$type}")
            ->line('الصفحة: '.$this->event->page_path)
            ->line('المصدر: '.$this->event->sourceLabel())
            ->line('الجهاز: '.($this->event->device ?? 'غير معروف'))
            ->line('الوقت: '.$this->event->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i'))
            ->salutation('مكتب المحامي ريان الجهني');
    }
}
