<?php

namespace App\Models;

use App\Support\Url;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['from_path', 'to_path', 'status_code', 'is_active', 'note'])]
class Redirect extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'status_code' => 'integer',
            'hits' => 'integer',
            'last_hit_at' => 'datetime',
        ];
    }

    /**
     * Paths are normalised on the way in so a rule entered as
     * "https://old.site/الخدمات/" matches a request for "/الخدمات".
     */
    public function setFromPathAttribute(?string $value): void
    {
        $this->attributes['from_path'] = $value === null ? null : Url::normalizePath($value);
    }

    public function setToPathAttribute(?string $value): void
    {
        // An absolute URL to another host is left alone; only internal paths
        // are normalised.
        if ($value !== null && preg_match('#^https?://#i', $value)) {
            $this->attributes['to_path'] = $value;

            return;
        }

        $this->attributes['to_path'] = $value === null ? null : Url::normalizePath($value);
    }

    public function isGone(): bool
    {
        return $this->status_code === 410;
    }

    /**
     * Where a matched request should be sent.
     */
    public function destination(): ?string
    {
        if ($this->isGone() || $this->to_path === null) {
            return null;
        }

        if (preg_match('#^https?://#i', $this->to_path)) {
            return $this->to_path;
        }

        return Url::canonical($this->to_path);
    }

    public function recordHit(): void
    {
        // Written without touching updated_at — a hit is traffic, not an edit.
        $this->newQuery()->whereKey($this->getKey())->update([
            'hits' => $this->hits + 1,
            'last_hit_at' => now(),
        ]);
    }
}
