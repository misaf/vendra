<?php

declare(strict_types=1);

use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages\ListPermissions;
use Misaf\VendraPermission\Models\Permission;
use Misaf\VendraPermission\Models\Role;
use Misaf\VendraPermission\Tests\Support\PermissionModuleTestContext;
use Morilog\Jalali\Jalalian;

use function Pest\Livewire\livewire;

$tenant = null;

beforeEach(function () use (&$tenant): void {
    $tenant = PermissionModuleTestContext::setUpFilamentAdminContext();
});

describe('table rendering', function () use (&$tenant): void {
    it('renders table', function (): void {
        livewire(ListPermissions::class)
            ->loadTable()
            ->assertSuccessful();
    });

    it('lists records', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $permissions = Permission::factory()
            ->count(2)
            ->forTenant($tenant)
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertCanSeeTableRecords($permissions);
    });

    it('counts records', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $permissions = Permission::factory()
            ->count(2)
            ->forTenant($tenant)
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertCountTableRecords($permissions->count());
    });

    it('paginates records', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $firstGroupPermissions = Permission::factory()
            ->count(10)
            ->forTenant($tenant)
            ->forGuard('web')
            ->sequence(fn (Sequence $sequence): array => ['name' => sprintf('a-permission-%02d', $sequence->index)])
            ->create();

        $otherGroupPermissions = Permission::factory()
            ->count(10)
            ->forTenant($tenant)
            ->forGuard('web')
            ->sequence(fn (Sequence $sequence): array => ['name' => sprintf('z-permission-%02d', $sequence->index)])
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertCanSeeTableRecords($otherGroupPermissions)
            ->assertCanNotSeeTableRecords($firstGroupPermissions)
            ->call('gotoPage', 2)
            ->assertCanSeeTableRecords($firstGroupPermissions)
            ->assertCanNotSeeTableRecords($otherGroupPermissions);
    });

    it('shows empty table when no records exist', function (): void {
        livewire(ListPermissions::class)
            ->loadTable()
            ->assertCountTableRecords(0);
    });
});

describe('table columns', function () use (&$tenant): void {
    it('renders columns', function (string $column) use (&$tenant): void {
        assert($tenant instanceof Model);

        Permission::factory()
            ->forTenant($tenant)
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertCanRenderTableColumn($column);
    })->with([
        'row column' => 'row',
        'roles relation' => 'roles.name',
        'name column' => 'name',
    ]);

    it('hides columns by default', function (string $column) use (&$tenant): void {
        assert($tenant instanceof Model);

        Permission::factory()
            ->forTenant($tenant)
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertCanNotRenderTableColumn($column);
    })->with([
        'description column' => 'description',
    ]);
});

describe('table search', function () use (&$tenant): void {
    it('searches records', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $firstPermission = Permission::factory()
            ->forTenant($tenant)
            ->create([
                'name' => 'alpha-permission-search',
            ]);

        $otherPermissions = Permission::factory()
            ->forTenant($tenant)
            ->create([
                'name' => 'beta-permission-search',
            ]);

        livewire(ListPermissions::class)
            ->loadTable()
            ->searchTable($firstPermission->name)
            ->assertCanSeeTableRecords([$firstPermission])
            ->assertCanNotSeeTableRecords([$otherPermissions]);
    });

    it('shows no records for unmatched search', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->searchTable('permission-name-that-does-not-exist')
            ->assertCanNotSeeTableRecords([$permission])
            ->assertCountTableRecords(0);
    });
});

describe('table sorting', function () use (&$tenant): void {
    it('sorts records', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $sortPrefix = uniqid('permission-sort-', true);

        $permissions = Permission::factory()
            ->count(3)
            ->forTenant($tenant)
            ->forGuard('web')
            ->sequence(
                ['name' => "{$sortPrefix}-c"],
                ['name' => "{$sortPrefix}-a"],
                ['name' => "{$sortPrefix}-b"],
            )
            ->create();

        $sortedPermissionsAsc = Permission::query()
            ->whereKey($permissions->pluck('id'))
            ->orderBy('name', 'asc')
            ->get();

        $sortedPermissionsDesc = Permission::query()
            ->whereKey($permissions->pluck('id'))
            ->orderBy('name', 'desc')
            ->get();

        livewire(ListPermissions::class)
            ->loadTable()
            ->sortTable('name', 'asc')
            ->assertCanSeeTableRecords($sortedPermissionsAsc, inOrder: true)
            ->sortTable('name', 'desc')
            ->assertCanSeeTableRecords($sortedPermissionsDesc, inOrder: true);
    });
});

describe('column states', function () use (&$tenant): void {
    it('sets row index state', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertTableColumnStateSet('row', 1, $permission);
    });

    it('sets roles relation state', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $role = Role::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->create();

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->forGuard($role->guard_name)
            ->create();

        $role->givePermissionTo($permission);

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertTableColumnStateSet('roles.name', $permission->roles()->pluck('name'), $permission);
    });

    it('sets raw column state', function (string $column) use (&$tenant): void {
        assert($tenant instanceof Model);

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertTableColumnStateSet($column, $permission->{$column}, $permission);
    })->with([
        'name column' => 'name',
        'created at column' => 'created_at',
        'updated at column' => 'updated_at',
    ]);

    it('formats row index state', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertTableColumnFormattedStateSet('row', 1, $permission);
    });

    it('formats roles relation state', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $role = Role::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->create();

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->forGuard($role->guard_name)
            ->create();

        $role->givePermissionTo($permission);

        $component = livewire(ListPermissions::class)
            ->loadTable();

        $column = $component->instance()->getTable()->getColumn('roles.name');

        expect($column)->toBeInstanceOf(TextColumn::class);

        assert($column instanceof TextColumn);

        $column->record($permission);
        $column->clearCachedState();

        expect($column->formatState($column->getState()))
            ->toBe($permission->roles()->pluck('name')->all());
    });

    it('formats name column state', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertTableColumnFormattedStateSet('name', $permission->name, $permission);
    });

    it('formats timestamp column state', function (string $column) use (&$tenant): void {
        assert($tenant instanceof Model);

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->create();

        $expected = app()->isLocale('fa')
            ? Jalalian::fromCarbon($permission->{$column})->format('Y-m-d H:i')
            : $permission->{$column}->format('Y-m-d H:i');

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertTableColumnFormattedStateSet($column, $expected, $permission);
    })->with([
        'created at column' => 'created_at',
        'updated at column' => 'updated_at',
    ]);
});

describe('column existence', function () use (&$tenant): void {
    it('resolves name description below', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertTableColumnExists('name', fn(TextColumn $column): bool => $column->getDescriptionBelow() === null, $permission);
    });

    it('has configured columns', function (string $column): void {
        livewire(ListPermissions::class)
            ->loadTable()
            ->assertTableColumnExists($column);
    })->with([
        'row column' => 'row',
        'roles relation' => 'roles.name',
        'description column' => 'description',
        'created at column' => 'created_at',
        'updated at column' => 'updated_at',
    ]);
});

describe('column visibility', function (): void {
    it('shows configured visible columns', function (string $column): void {
        livewire(ListPermissions::class)
            ->loadTable()
            ->assertTableColumnVisible($column);
    })->with([
        'row column' => 'row',
        'roles relation' => 'roles.name',
        'name column' => 'name',
        'created at column' => 'created_at',
        'updated at column' => 'updated_at',
    ]);
});

describe('column descriptions', function () use (&$tenant): void {
    it('sets description column state', function () use (&$tenant): void {
        assert($tenant instanceof Model);

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertTableColumnStateSet('description', $permission->description, $permission);
    });

    it('hides description below for all columns', function (string $column) use (&$tenant): void {
        assert($tenant instanceof Model);

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertTableColumnDoesNotHaveDescription($column, $permission->description, $permission, 'below');
    })->with([
        'row column' => 'row',
        'roles relation' => 'roles.name',
        'name column' => 'name',
        'description column' => 'description',
        'created at column' => 'created_at',
        'updated at column' => 'updated_at',
    ]);

    it('hides description above for all columns', function (string $column) use (&$tenant): void {
        assert($tenant instanceof Model);

        $permission = Permission::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->assertTableColumnDoesNotHaveDescription($column, $permission->description, $permission, 'above');
    })->with([
        'row column' => 'row',
        'roles relation' => 'roles.name',
        'name column' => 'name',
        'description column' => 'description',
        'created at column' => 'created_at',
        'updated at column' => 'updated_at',
    ]);
});

describe('toggleable columns', function () use (&$tenant): void {
    it('shows hidden columns after toggle', function (string $column) use (&$tenant): void {
        assert($tenant instanceof Model);

        Permission::factory()
            ->forTenant($tenant)
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->toggleAllTableColumns()
            ->assertCanRenderTableColumn($column);
    })->with([
        'description column' => 'description',
    ]);

    it('keeps hidden columns hidden after toggle off', function (string $column) use (&$tenant): void {
        assert($tenant instanceof Model);

        Permission::factory()
            ->forTenant($tenant)
            ->create();

        livewire(ListPermissions::class)
            ->loadTable()
            ->toggleAllTableColumns(false)
            ->assertCanNotRenderTableColumn($column);
    })->with([
        'description column' => 'description',
    ]);
});
