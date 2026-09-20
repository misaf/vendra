<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Tests\Feature\TeamScopeTenantRecord;

beforeEach(function (): void {
    Schema::create('team_scope_tenant_records', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('tenant_id')->nullable();
    });
});

it('reports whether a record belongs to a tenant', function (): void {
    $tenantRecord = new TeamScopeTenantRecord(['tenant_id' => 1]);
    $platformRecord = new TeamScopeTenantRecord(['tenant_id' => null]);

    expect($tenantRecord->hasTenant())->toBeTrue()
        ->and($platformRecord->hasTenant())->toBeFalse()
        ->and(new TeamScopeTenantRecord()->hasTenant())->toBeFalse();
});

it('reads the tenant membership from the configured foreign key', function (): void {
    config()->set('vendra-tenant.foreign_key', 'workspace_id');

    $record = new TeamScopeTenantRecord(['tenant_id' => 1]);

    expect($record->hasTenant())->toBeFalse()
        ->and($record->setAttribute('workspace_id', 7)->hasTenant())->toBeTrue();
});
