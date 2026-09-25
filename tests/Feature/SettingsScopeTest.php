<?php

declare(strict_types=1);

use App\Models\SettingsProperty;
use App\Settings\GeneralSettings;
use App\Settings\SettingsScope;
use Illuminate\Support\Facades\DB;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Settings\StoreCreationSettings;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;

/**
 * Count a property's raw rows across every scope, so duplicates are visible.
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
        expect(resolve(StoreCreationSettings::class)->open)->toBeTrue()
            ->and(settingsRowCount('store_creation', 'open', SettingsScope::PLATFORM))->toBe(1);
    });

    it('persists a platform setting with no tenant', function (): void {
        resolve(StoreCreationSettings::class)->fill(['open' => false])->save();
        app()->forgetInstance(StoreCreationSettings::class);

        expect(resolve(StoreCreationSettings::class)->open)->toBeFalse();

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
            resolve(StoreCreationSettings::class)->fill(['open' => $open])->save();
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

        resolve(StoreCreationSettings::class)->fill(['open' => false])->save();

        expect(settingsRowCount('store_creation', 'open'))->toBe(1)
            ->and(settingsRowCount('store_creation', 'open', SettingsScope::PLATFORM))->toBe(1);
    });
});

describe('store settings', function (): void {
    it('falls back to the platform row until the store saves its own', function (): void {
        $platformName = [];
        Store::factory()->active()->create()->makeCurrent();

        expect(resolve(GeneralSettings::class)->name)->toBe($platformName)
            ->and(settingsRowCount('general', 'name'))->toBe(1);
    });

    it('writes a store row without touching the platform default', function (): void {
        $platformName = [];
        $store = Store::factory()->active()->create();
        $store->makeCurrent();

        resolve(GeneralSettings::class)->fill(['name' => ['en' => 'Acme Flowers']])->save();

        expect(settingsRowCount('general', 'name', SettingsScope::forTenant($store->id)))->toBe(1)
            ->and(settingsRowCount('general', 'name', SettingsScope::PLATFORM))->toBe(1);

        $storedPlatformName = SettingsProperty::query()
            ->withoutGlobalScopes()
            ->where('scope', SettingsScope::PLATFORM)
            ->where('group', 'general')
            ->where('name', 'name')
            ->value('payload');

        expect($storedPlatformName)->toBe(json_encode($platformName));
    });

    it('never grows a second row for a store however often it is saved', function (): void {
        $store = Store::factory()->active()->create();
        $store->makeCurrent();

        foreach (['One', 'Two', 'Three'] as $title) {
            resolve(GeneralSettings::class)->fill(['name' => ['en' => $title]])->save();
        }

        expect(settingsRowCount('general', 'name', SettingsScope::forTenant($store->id)))->toBe(1)
            ->and(resolve(GeneralSettings::class)->name)->toBe(['en' => 'Three']);
    });

    it('keeps one store out of another store settings', function (): void {
        $first = Store::factory()->active()->create();
        $second = Store::factory()->active()->create();

        $first->makeCurrent();
        resolve(GeneralSettings::class)->fill(['name' => ['en' => 'First']])->save();

        $second->makeCurrent();
        resolve(GeneralSettings::class)->fill(['name' => ['en' => 'Second']])->save();

        $first->makeCurrent();
        expect(resolve(GeneralSettings::class)->name)->toBe(['en' => 'First']);

        $second->makeCurrent();
        expect(resolve(GeneralSettings::class)->name)->toBe(['en' => 'Second']);
    });

    /*
     | Settings objects are scoped container instances, so without the switch
     | task the first store read in a process would answer for every store
     | switched to after it.
     */
    it('re-reads settings when the current tenant changes', function (): void {
        $platformName = [];
        $first = Store::factory()->active()->create();
        $second = Store::factory()->active()->create();

        $first->makeCurrent();
        resolve(GeneralSettings::class)->fill(['name' => ['en' => 'First']])->save();
        expect(resolve(GeneralSettings::class)->name)->toBe(['en' => 'First']);

        $second->makeCurrent();
        expect(resolve(GeneralSettings::class)->name)->toBe($platformName);

        Store::forgetCurrent();
        expect(resolve(GeneralSettings::class)->name)->toBe($platformName);
    });
});

/*
 | `vendra-tenant:enable` backfills every null tenant id in a registered table
 | and then forces the column NOT NULL. That is exactly what a platform settings
 | row must never receive, so `settings` stays out of the registry.
 */
it('keeps the tenancy retrofit away from platform settings rows', function (): void {
    $tables = array_column(resolve(TenantTableRegistry::class)->all(), 'table');

    expect($tables)->not->toContain('settings');
});

describe('store creation settings', function (): void {
    it('answers from the platform setting outside any tenant', function (): void {
        expect(resolve(StoreCreationSettings::class)->open)->toBeTrue();

        resolve(StoreCreationSettings::class)->fill(['open' => false])->save();
        app()->forgetInstance(StoreCreationSettings::class);

        expect(resolve(StoreCreationSettings::class)->open)->toBeFalse();
    });
});
