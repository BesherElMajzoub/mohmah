<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds the settings *keys*, not invented values.
 *
 * Every field the client has not supplied is seeded empty on purpose. An
 * empty value makes Settings::filled() false, which hides the whole block
 * from the public site — so the footer simply has no address section until a
 * real address is entered, rather than showing a plausible-looking
 * placeholder that a visitor might act on.
 *
 * Only firstOrCreate is used, so re-running never overwrites values the
 * client has already entered.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // --- Contact: all awaiting real values from the client ---------
            ['key' => 'office_address', 'value' => null, 'type' => 'text', 'group' => 'contact'],
            ['key' => 'map_url', 'value' => null, 'type' => 'string', 'group' => 'contact'],
            ['key' => 'office_hours', 'value' => null, 'type' => 'text', 'group' => 'contact'],
            // Machine-readable hours for JSON-LD, e.g. "Su-Th 09:00-17:00".
            ['key' => 'office_hours_schema', 'value' => null, 'type' => 'string', 'group' => 'contact'],
            ['key' => 'office_email', 'value' => null, 'type' => 'string', 'group' => 'contact'],

            // --- Social: only real, approved accounts ever go here ---------
            ['key' => 'social_links', 'value' => '{}', 'type' => 'json', 'group' => 'contact'],

            // --- Contact form: off until the client asks for it ------------
            ['key' => 'contact_form_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'contact'],

            // --- Conversion alerts -----------------------------------------
            ['key' => 'alerts_email', 'value' => null, 'type' => 'string', 'group' => 'alerts'],
            ['key' => 'alerts_digest_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'alerts'],
            ['key' => 'alerts_instant_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'alerts'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
