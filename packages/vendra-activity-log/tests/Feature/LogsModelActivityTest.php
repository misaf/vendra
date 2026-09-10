<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraActivityLog\Tests\Fixtures\LoggableWidget;
use Misaf\VendraActivityLog\Tests\Fixtures\PlainWidget;
use Misaf\VendraSupport\Context\RequestJobContext;

beforeEach(function (): void {
    if (! Schema::hasTable('activity_log_widgets')) {
        (require __DIR__.'/../database/migrations/0001_01_01_000000_create_activity_log_widgets_table.php')->up();
    }

    makeCurrentTestTenant();

    config(['activitylog.enabled' => true]);
});

it('logs creation for a model that implements ShouldLogActivity', function (): void {
    Context::add(RequestJobContext::TRACE_ID, 'activity-trace');

    $widget = LoggableWidget::query()->create(['name' => 'Alpha', 'description' => 'first']);

    $activity = DB::table('activity_log')->latest('id')->first();

    expect($activity)->not->toBeNull()
        ->and($activity->event)->toBe('created')
        ->and($activity->description)->toBe('created')
        ->and($activity->subject_type)->toBe($widget->getMorphClass())
        ->and((int) $activity->subject_id)->toBe($widget->id);

    $changes = json_decode($activity->attribute_changes, true);
    $properties = json_decode($activity->properties, true);

    expect(Arr::get($changes, 'attributes'))->toBe(['name' => 'Alpha', 'description' => 'first'])
        ->and(Arr::get($changes, 'attributes'))->not->toHaveKey('id')
        ->and($properties[RequestJobContext::TRACE_ID])->toBe('activity-trace');
});

it('logs updates with both old and new attribute values', function (): void {
    $widget = LoggableWidget::query()->create(['name' => 'Alpha']);
    $widget->update(['name' => 'Beta']);

    $activity = DB::table('activity_log')->where('event', 'updated')->latest('id')->first();
    $changes = json_decode($activity->attribute_changes, true);

    expect(Arr::get($changes, 'attributes.name'))->toBe('Beta')
        ->and(Arr::get($changes, 'old.name'))->toBe('Alpha');
});

it('logs deletion', function (): void {
    $widget = LoggableWidget::query()->create(['name' => 'Alpha']);
    $widget->delete();

    expect(DB::table('activity_log')->where('event', 'deleted')->exists())->toBeTrue();
});

it('does not log models that do not implement ShouldLogActivity', function (): void {
    PlainWidget::query()->create(['name' => 'Quiet']);

    expect(DB::table('activity_log')->count())->toBe(0);
});

it('records nothing while activity logging is disabled', function (): void {
    config()->set('activitylog.enabled', false);

    LoggableWidget::query()->create(['name' => 'Alpha']);

    expect(DB::table('activity_log')->count())->toBe(0);
});
