<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Misaf\VendraActivityLog\Filament\Clusters\Resources\Pages\ViewActivityLog;
use Misaf\VendraActivityLog\Models\ActivityLog;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();
});

it('renders the view activity log page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $activityLog = ActivityLog::create([
        'log_name' => 'default',
        'description' => 'test',
    ]);

    livewire(ViewActivityLog::class, ['record' => $activityLog->getKey()])
        ->assertOk();
});
