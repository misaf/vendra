<?php

declare(strict_types=1);

use App\Settings\SettingsScope;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Key the store name and description by locale.
 *
 * A store keeps the title it saved under the fallback locale. The platform
 * default becomes empty, so a store that never named itself falls back to its
 * own name rather than the platform's.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $locale = Config::string('app.fallback_locale');
        $renames = ['site_title' => 'name', 'site_description' => 'description'];

        foreach ($renames as $from => $to) {
            DB::table('settings')
                ->where('group', 'general')
                ->where('name', $from)
                ->get()
                ->each(function (object $row) use ($locale, $to): void {
                    $value = $row->scope === SettingsScope::PLATFORM || ! is_string($row->payload) ? null : json_decode($row->payload, true);

                    DB::table('settings')->where('id', $row->id)->update([
                        'name' => $to,
                        'payload' => json_encode(is_string($value) && $value !== '' ? [$locale => $value] : []),
                    ]);
                });
        }

        if (! $this->migrator->exists('general.name')) {
            $this->migrator->add('general.name', []);
        }

        if (! $this->migrator->exists('general.description')) {
            $this->migrator->add('general.description', []);
        }
    }
};
