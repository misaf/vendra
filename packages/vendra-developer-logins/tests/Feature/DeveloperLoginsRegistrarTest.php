<?php

declare(strict_types=1);

use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Config;
use Misaf\VendraDeveloperLogins\Support\DeveloperLoginsRegistrar;
use Misaf\VendraPermission\Models\Role;
use Misaf\VendraUser\Models\User;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('registers developer logins only on configured panels', function (): void {
    expect(Filament::getPanel('admin')->hasPlugin('filament-developer-logins'))->toBeTrue()
        ->and(Filament::getPanels())->not->toHaveKey('user');
});

it('builds the upstream plugin with the Vendra user model', function (): void {
    $plugin = DeveloperLoginsRegistrar::make();

    expect($plugin)->toBeInstanceOf(FilamentDeveloperLoginsPlugin::class)
        ->and($plugin->getModelClass())->toBe(User::class)
        ->and($plugin->getColumn())->toBe('email')
        ->and($plugin->getSwitchable())->toBeTrue()
        ->and($plugin->getEnabled())->toBeFalse();
});

it('provides only users assigned to the configured admin role', function (): void {
    $role = Role::factory()->forGuard('web')->create([
        'name' => Config::string('vendra-permission.admin_role'),
    ]);

    $admin = User::factory()->create();
    $admin->assignRole($role);

    User::factory()->create();

    expect(DeveloperLoginsRegistrar::users())->toBe([
        $admin->username => $admin->email,
    ])->and(DeveloperLoginsRegistrar::hasUsers())->toBeTrue();
});

it('returns no developer logins when the configured role does not exist', function (): void {
    Config::set('vendra-developer-logins.role', 'missing-role');

    expect(DeveloperLoginsRegistrar::users())->toBeEmpty()
        ->and(DeveloperLoginsRegistrar::hasUsers())->toBeFalse();
});

it('omits users when configured login attributes are not strings', function (): void {
    $role = Role::factory()->forGuard('web')->create([
        'name' => Config::string('vendra-permission.admin_role'),
    ]);

    User::factory()->create()->assignRole($role);
    Config::set('vendra-developer-logins.label_column', 'id');

    expect(DeveloperLoginsRegistrar::users())->toBeEmpty();
});
