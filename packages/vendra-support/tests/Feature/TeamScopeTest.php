<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Tests\Feature\TeamScopePlainRecord;
use Misaf\VendraSupport\Tests\Feature\TeamScopeTenantRecord;
use Misaf\VendraSupport\Tests\Feature\TeamScopeUser;

beforeEach(function (): void {
    Schema::create('team_scope_users', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('tenant_id')->nullable();
    });

    Schema::create('team_scope_tenant_records', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('tenant_id')->nullable();
    });

    Schema::create('team_scope_plain_records', function (Blueprint $table): void {
        $table->id();
    });
});

it('skips models whose table has no tenant column', function (): void {
    TeamScopePlainRecord::query()->insert([['id' => 1], ['id' => 2]]);

    $this->actingAs(TeamScopeUser::query()->create(['tenant_id' => 1]));

    expect(TeamScopePlainRecord::query()->toSql())->not->toContain('tenant_id')
        ->and(TeamScopePlainRecord::query()->count())->toBe(2);
});

it('constrains tenant-aware models to the authenticated user tenant when no tenant is current', function (): void {
    TeamScopeTenantRecord::query()->insert([
        ['id' => 1, 'tenant_id' => 1],
        ['id' => 2, 'tenant_id' => 2],
    ]);

    $this->actingAs(TeamScopeUser::query()->create(['tenant_id' => 1]));

    $records = TeamScopeTenantRecord::query()->get();

    expect($records)->toHaveCount(1)
        ->and($records->sole()->getAttribute('tenant_id'))->toBe(1);
});

it('does not constrain tenant-aware models for an authenticated identity without a tenant', function (): void {
    TeamScopeTenantRecord::query()->insert([
        ['id' => 1, 'tenant_id' => 1],
        ['id' => 2, 'tenant_id' => 2],
    ]);

    $consoleUser = new class extends Authenticatable
    {
        use HasFactory;
    };
    $consoleUser->setAttribute('id', 1);

    $this->actingAs($consoleUser);

    expect(TeamScopeTenantRecord::query()->count())->toBe(2);
});
