<?php

namespace App\Observers;

use App\Support\Settings;

/**
 * The settings table is cached forever, so any write has to invalidate it —
 * otherwise the client changes the office phone number and the site keeps
 * serving the old one indefinitely.
 */
class SettingsCacheObserver
{
    public function __construct(private readonly Settings $settings) {}

    public function saved(): void
    {
        $this->settings->flush();
    }

    public function deleted(): void
    {
        $this->settings->flush();
    }
}
