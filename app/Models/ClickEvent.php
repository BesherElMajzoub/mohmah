<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'type', 'page_path', 'page_type', 'placement',
    'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
    'gclid', 'referrer', 'landing_path',
    'device', 'user_agent', 'ip_hash', 'visitor_id',
])]
class ClickEvent extends Model
{
    public const TYPE_CALL = 'call';

    public const TYPE_WHATSAPP = 'whatsapp';

    public const TYPE_CONTACT_FORM = 'contact_form';

    public const TYPES = [self::TYPE_CALL, self::TYPE_WHATSAPP, self::TYPE_CONTACT_FORM];

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_CALL => 'اتصال',
            self::TYPE_WHATSAPP => 'واتساب',
            self::TYPE_CONTACT_FORM => 'نموذج التواصل',
            default => $type,
        };
    }

    public function scopeBetween(Builder $query, \DateTimeInterface $from, \DateTimeInterface $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $query->when($type, fn (Builder $q) => $q->where('type', $type));
    }

    /**
     * A human-readable traffic source.
     *
     * gclid is checked first because a Google Ads click is the answer the
     * office most wants; the referrer is only consulted when there are no
     * campaign parameters at all.
     */
    public function sourceLabel(): string
    {
        if (filled($this->gclid)) {
            return 'إعلانات جوجل';
        }

        if (filled($this->utm_source)) {
            return $this->utm_source.($this->utm_campaign ? " / {$this->utm_campaign}" : '');
        }

        if (filled($this->referrer)) {
            return parse_url($this->referrer, PHP_URL_HOST) ?: $this->referrer;
        }

        return 'زيارة مباشرة';
    }
}
