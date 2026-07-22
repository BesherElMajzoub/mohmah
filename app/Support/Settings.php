<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Reads the settings table as one cached array.
 *
 * Header, footer and every page touch settings, so a per-key query would mean
 * dozens of round trips per request. The whole table is small enough to cache
 * whole and flush on any write.
 *
 * The important behaviour here is `filled()`. Large parts of the site — the
 * address block, the map, opening hours, social links — must stay completely
 * hidden until the client supplies real values, rather than rendering an
 * invented placeholder. Templates ask `filled()` and omit the section
 * entirely when it is false.
 */
class Settings
{
    public const CACHE_KEY = 'settings.all';

    /** @var array<string, mixed>|null */
    private ?array $cached = null;

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->cached ??= Cache::rememberForever(
            self::CACHE_KEY,
            fn () => Setting::query()
                ->get()
                ->mapWithKeys(fn (Setting $s) => [$s->key => $s->castValue()])
                ->all()
        );
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * True when the setting holds a real, non-empty value.
     *
     * This is the guard that keeps unsupplied facts off the public site.
     */
    public function filled(string $key): bool
    {
        $value = $this->get($key);

        if (is_array($value)) {
            return $value !== [];
        }

        return $value !== null && trim((string) $value) !== '';
    }

    public function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => in_array($type, ['json', 'array'], true) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value,
                'type' => $type,
                'group' => $group,
            ],
        );

        $this->flush();
    }

    public function flush(): void
    {
        $this->cached = null;
        Cache::forget(self::CACHE_KEY);
    }
}
