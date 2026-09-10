<?php

declare(strict_types=1);

use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Misaf\VendraActivityLog\Filament\Clusters\Resources\Pages\ListActivityLogs;
use Misaf\VendraActivityLog\Models\ActivityLog;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();
});

it('sorts the activity logs table by every sortable column following the stored values', function (): void {
    activity('aaa-log')->log('first entry');
    activity('bbb-log')->log('second entry');

    $activityLogs = ActivityLog::query()->orderBy('id')->get()->all();

    expect(livewire(ListActivityLogs::class)->call('loadTable'))
        ->toSortByEverySortableColumn($activityLogs);
});

it('renders activity identifiers as suffix badges', function (): void {
    config(['activitylog.enabled' => true]);

    activity('aaa-log')->log('test entry');

    $activityLog = ActivityLog::query()->firstOrFail();
    $activityLog->forceFill([
        'subject_type' => 'test-subject',
        'subject_id' => 42,
        'causer_type' => 'test-causer',
        'causer_id' => 24,
    ])->save();

    $component = livewire(ListActivityLogs::class)->call('loadTable');

    foreach (['subject_type' => '42', 'causer_type' => '24'] as $columnName => $identifier) {
        $component->assertTableColumnExists(
            $columnName,
            function (BadgeableColumn $column) use ($columnName, $identifier): bool {
                $formattedState = (string) $column->formatState($columnName);

                return str_contains($formattedState, 'badgeable-column-badge')
                    && str_contains($formattedState, $identifier);
            },
            $activityLog,
        );
    }
});
