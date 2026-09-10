<?php

declare(strict_types=1);

use App\Models\SettingsProperty;
use App\Settings\GeneralSettings;
use App\Settings\SettingsScope;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Settings\StoreCreationSettings;
use Misaf\VendraStore\Support\StoreCreationPolicy;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;

/**
 * Count the raw rows behind a property, ignoring every scope, so a duplicate
 * the repository would happily read past is still visible to the test.
 */
function settingsRowCount(string $group, string $name, ?string $scope = null): int
{
    $query = DB::table('settings')->where('group', $group)->where('name', $name);

    if ($scope !== null) {
        $query->where('scope', $scope);
    }

    return $query->count();
}

describe('platform settings', function (): void {
    it('reads its defaults on a fresh install without seeding', function (): void {
        expect(app(StoreCreationSettings::class)->open)->toBeTrue()
            ->and(settingsRowCount('store_creation', 'open', SettingsScope::PLATFORM))->toBe(1);
    });

    it('persists a platform setting with no tenant', function (): void {
        app(StoreCreationSettings::class)->fill(['open' => false])->save();
        app()->forgetInstance(StoreCreationSettings::class);

        expect(app(StoreCreationSettings::class)->open)->toBeFalse();

        $row = SettingsProperty::query()
            ->withoutGlobalScopes()
            ->where('group', 'store_creation')
            ->where('name', 'open')
            ->sole();

        expect($row->getAttribute('scope'))->toBe(SettingsScope::PLATFORM)
            ->and($row->getAttribute('tenant_id'))->toBeNull();
    });

    it('never grows a second global row however often it is saved', function (): void {
        foreach ([false, true, false] as $open) {
            app(StoreCreationSettings::class)->fill(['open' => $open])->save();
        }

        expect(settingsRowCount('store_creation', 'open'))->toBe(1);
    });

    /*
     | The console and reseller panels run outside tenancy, but a queued job or
     | an `eachTenant()` pass can save a platform setting with a store current.
     | It must still land on the platform row rather than forking a tenant copy.
     */
    it('stays on the platform row while a store is current', function (): void {
        Store::factory()->active()->create()->makeCurrent();

        app(StoreCreationSettings::class)->fill(['open' => false])->save();

        expect(settingsRowCount('store_creation', 'open'))->toBe(1)
            ->and(settingsRowCount('store_creation', 'open', SettingsScope::PLATFORM))->toBe(1);
    });
});

describe('store settings', function (): void {
    it('falls back to the platform row until the store saves its own', function (): void {
        $platformTitle = Config::string('app.name');
        Store::factory()->active()->create()->makeCurrent();

        expect(app(GeneralSettings::class)->site_title)->toBe($platformTitle)
            ->and(settingsRowCount('general', 'site_title'))->toBe(1);
    });

    it('writes a store row without touching the platform default', function (): void {
        $platformTitle = Config::string('app.name');
        $store = Store::factory()->active()->create();
        $store->makeCurrent();

        app(GeneralSettings::class)->fill(['site_title' => 'Acme Flowers'])->save();

        expect(settingsRowCount('general', 'site_title', SettingsScope::forTenant($store->id)))->toBe(1)
            ->and(settingsRowCount('general', 'site_title', SettingsScope::PLATFORM))->toBe(1);

        $storedPlatformTitle = SettingsProperty::query()
            ->withoutGlobalScopes()
            ->where('scope', SettingsScope::PLATFORM)
            ->where('group', 'general')
            ->where('name', 'site_title')
            ->value('payload');

        expect($storedPlatformTitle)->toBe(json_encode($platformTitle));
    });

    it('never grows a second row for a store however often it is saved', function (): void {
        $store = Store::factory()->active()->create();
        $store->makeCurrent();

        foreach (['One', 'Two', 'Three'] as $title) {
            app(GeneralSettings::class)->fill(['site_title' => $title])->save();
        }

        expect(settingsRowCount('general', 'site_title', SettingsScope::forTenant($store->id)))->toBe(1)
            ->and(app(GeneralSettings::class)->site_title)->toBe('Three');
    });

    it('keeps one store out of another store settings', function (): void {
        $first = Store::factory()->active()->create();
        $second = Store::factory()->active()->create();

        $first->makeCurrent();
        app(GeneralSettings::class)->fill(['site_title' => 'First'])->save();

        $second->makeCurrent();
        app(GeneralSettings::class)->fill(['site_title' => 'Second'])->save();

        $first->makeCurrent();
        expect(app(GeneralSettings::class)->site_title)->toBe('First');

        $second->makeCurrent();
        expect(app(GeneralSettings::class)->site_title)->toBe('Second');
    });

    /*
     | Settings objects are scoped container instances, so without the switch
     | task the first store read in a process would answer for every store
     | switched to after it.
     */
    it('re-reads settings when the current tenant changes', function (): void {
        $platformTitle = Config::string('app.name');
        $first = Store::factory()->active()->create();
        $second = Store::factory()->active()->create();

        $first->makeCurrent();
        app(GeneralSettings::class)->fill(['site_title' => 'First'])->save();
        expect(app(GeneralSettings::class)->site_title)->toBe('First');

        $second->makeCurrent();
        expect(app(GeneralSettings::class)->site_title)->toBe($platformTitle);

        Store::forgetCurrent();
        expect(app(GeneralSettings::class)->site_title)->toBe($platformTitle);
    });
});

/*
 | `vendra-tenant:enable` backfills every null tenant id in a registered table
 | and then forces the column NOT NULL. That is exactly what a platform settings
 | row must never receive, so `settings` stays out of the registry.
 */
it('keeps the tenancy retrofit away from platform settings rows', function (): void {
    $tables = array_column(app(TenantTableRegistry::class)->all(), 'table');

    expect($tables)->not->toContain('settings');
});

describe('store creation policy', function (): void {
    it('answers from the platform setting outside any tenant', function (): void {
        expect(app(StoreCreationPolicy::class)->isOpen())->toBeTrue();

        app(StoreCreationSettings::class)->fill(['open' => false])->save();
        app()->forgetInstance(StoreCreationSettings::class);
        app()->forgetInstance(StoreCreationPolicy::class);

        expect(app(StoreCreationPolicy::class)->isOpen())->toBeFalse();
    });
});
