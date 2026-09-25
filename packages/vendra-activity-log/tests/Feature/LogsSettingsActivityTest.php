<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Misaf\VendraActivityLog\Listeners\LogSettingsActivity;
use Misaf\VendraActivityLog\Tests\Fixtures\LoggableSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;

beforeEach(function (): void {
    $migrator = resolve(SettingsMigrator::class)->repository('global');
    $migrator->add('activity_log_fixture.name', 'Alpha');
    $migrator->add('activity_log_fixture.limit', 10);
});

/**
 * Make a store current before logging is on, so creating the store is not logged too.
 */
function enableActivityLogInStore(): void
{
    makeCurrentTestTenant();

    config(['activitylog.enabled' => true]);
}

it('logs only the store settings a save changes, with their old and new values', function (): void {
    enableActivityLogInStore();

    (new LoggableSettings)->fill(['name' => 'Beta', 'limit' => 10])->save();

    $activity = DB::table('activity_log')->sole();
    $changes = json_decode($activity->attribute_changes, true);

    expect($activity->log_name)->toBe(LogSettingsActivity::LOG_NAME)
        ->and($activity->event)->toBe('updated')
        ->and($activity->description)->toBe('activity_log_fixture')
        ->and($activity->subject_type)->toBeNull()
        ->and(Arr::get($changes, 'attributes'))->toBe(['name' => 'Beta'])
        ->and(Arr::get($changes, 'old'))->toBe(['name' => 'Alpha']);
});

it('logs nothing when a save changes no value', function (): void {
    enableActivityLogInStore();

    (new LoggableSettings)->fill(['name' => 'Alpha', 'limit' => 10])->save();

    expect(DB::table('activity_log')->count())->toBe(0);
});

it('logs a save made without a current store as platform activity', function (): void {
    config(['activitylog.enabled' => true]);

    (new LoggableSettings)->fill(['name' => 'Beta', 'limit' => 10])->save();

    expect(DB::table('activity_log')->sole()->tenant_id)->toBeNull();
});
