<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages\CreatePermission;
use Misaf\VendraPermission\Models\Permission;
use Misaf\VendraPermission\Tests\Support\PermissionModuleTestContext;

use function Pest\Livewire\livewire;

$tenant = null;

beforeEach(function () use (&$tenant): void {
    $tenant = PermissionModuleTestContext::setUpFilamentAdminContext();
});

describe('Filling a form in a test', function () use (&$tenant): void {
    it('#1', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->make();

        livewire(CreatePermission::class)
            ->fillForm([
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
                'description' => $permission->description,
            ])
            ->call('create');

        expect(
            Permission::query()
                ->where([
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name,
                    'description' => $permission->description,
                ])
                ->exists()
        )->toBeTrue();
    });
});

describe('Testing form validation', function () use (&$tenant): void {
    it('#2', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->make();

        livewire(CreatePermission::class)
            ->fillForm([
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
                'description' => $permission->description,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    })->with([
        'name column' => 'name',
        'guard_name column' => 'guard_name',
        'description column' => 'description',
    ]);

    it('#3', function (string $column) use (&$tenant): void {
        assert($tenant instanceof Model);

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->make();

        livewire(CreatePermission::class)
            ->fillForm([
                'name' => null,
                'guard_name' => $permission->guard_name,
                'description' => $permission->description,
            ])
            ->call('create')
            ->assertHasFormErrors([$column => 'required']);
    })->with([
        'name column' => 'name',
        'guard_name column' => 'guard_name',
    ]);
});

describe('Testing the existence of a form', function (): void {
    it('#4', function (): void {
        livewire(CreatePermission::class)
            ->assertSchemaExists('form');
    });
});

describe('Testing the existence of form fields', function (): void {
    it('#5', function (string $column): void {
        livewire(CreatePermission::class)
            ->assertFormFieldExists($column);
    })->with([
        'roles',
        'name',
        'guard_name',
        'description',
    ]);
});

describe('Testing the visibility of form fields', function (): void {
    it('#5', function (string $column): void {
        livewire(CreatePermission::class)
            ->assertFormFieldVisible($column);
    })->with([
        'name',
        'guard_name',
        'description',
    ]);
});
