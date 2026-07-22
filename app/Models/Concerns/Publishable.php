<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Draft / scheduled / published lifecycle.
 *
 * `published()` is the single definition of "visible to the public", used by
 * the public controllers AND by the sitemap. Keeping one scope is what
 * guarantees the sitemap can never advertise a URL that returns a 404 — a
 * scheduled article is excluded from both by the same clause.
 */
trait Publishable
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_PUBLISHED = 'published';

    /**
     * A record is public when it is approved AND its date has arrived.
     *
     * 'scheduled' counts as approved: the client has finished the article and
     * chosen a date. The clock, not a background job, is what makes it live —
     * so a scheduled article appears at its date even if the scheduler is not
     * running. The scheduler only tidies the stored status afterwards.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_SCHEDULED])
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Published AND allowed in the index — the exact set the sitemap lists.
     */
    public function scopeForSitemap(Builder $query): Builder
    {
        return $query->published()->where('is_indexable', true);
    }

    /**
     * Mirrors scopePublished() for a single loaded record.
     */
    public function isPublished(): bool
    {
        return in_array($this->status, [self::STATUS_PUBLISHED, self::STATUS_SCHEDULED], true)
            && ($this->published_at === null || $this->published_at->isPast());
    }

    /**
     * Approved and dated, but the date has not arrived yet.
     */
    public function isScheduled(): bool
    {
        return in_array($this->status, [self::STATUS_PUBLISHED, self::STATUS_SCHEDULED], true)
            && $this->published_at !== null
            && $this->published_at->isFuture();
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->isScheduled() => 'مجدول',
            $this->isPublished() => 'منشور',
            default => 'مسودة',
        };
    }
}
