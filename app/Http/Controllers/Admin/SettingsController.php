<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Global site settings.
 *
 * Every field here is optional, and leaving one blank is a supported answer,
 * not an incomplete form: a blank address means the site renders no address
 * block at all. That is deliberate — better an absent section than an
 * invented one on a law office's site.
 */
class SettingsController extends Controller
{
    public function __construct(private readonly Settings $settings) {}

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'values' => $this->settings->all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'office_address' => ['nullable', 'string', 'max:500'],
            'map_url' => ['nullable', 'url', 'max:500'],
            'office_hours' => ['nullable', 'string', 'max:300'],
            'office_hours_schema' => ['nullable', 'string', 'max:190'],
            'office_email' => ['nullable', 'email', 'max:190'],

            // Repeatable label/URL pairs.
            'social' => ['nullable', 'array'],
            'social.*.label' => ['nullable', 'string', 'max:60'],
            'social.*.url' => ['nullable', 'url', 'max:255'],

            'contact_form_enabled' => ['boolean'],
            'alerts_email' => ['nullable', 'email', 'max:190'],
            'alerts_digest_enabled' => ['boolean'],
            'alerts_instant_enabled' => ['boolean'],
        ]);

        foreach (['office_address', 'office_hours'] as $key) {
            $this->settings->set($key, $data[$key] ?? null, 'text', 'contact');
        }

        foreach (['map_url', 'office_hours_schema', 'office_email'] as $key) {
            $this->settings->set($key, $data[$key] ?? null, 'string', 'contact');
        }

        // Only complete pairs are stored, so a half-filled row cannot become
        // a social link with an empty label or a dead URL.
        $social = collect($data['social'] ?? [])
            ->filter(fn ($row) => filled($row['label'] ?? null) && filled($row['url'] ?? null))
            ->mapWithKeys(fn ($row) => [$row['label'] => $row['url']])
            ->all();

        $this->settings->set('social_links', $social, 'json', 'contact');

        $this->settings->set('contact_form_enabled', $request->boolean('contact_form_enabled') ? '1' : '0', 'boolean', 'contact');
        $this->settings->set('alerts_email', $data['alerts_email'] ?? null, 'string', 'alerts');
        $this->settings->set('alerts_digest_enabled', $request->boolean('alerts_digest_enabled') ? '1' : '0', 'boolean', 'alerts');
        $this->settings->set('alerts_instant_enabled', $request->boolean('alerts_instant_enabled') ? '1' : '0', 'boolean', 'alerts');

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'تم حفظ الإعدادات.');
    }
}
