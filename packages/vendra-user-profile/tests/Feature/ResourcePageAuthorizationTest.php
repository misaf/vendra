<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Filament\Facades\Filament;
use Illuminate\Foundation\Auth\User as AuthenticatableUser;
use Illuminate\Support\Facades\Gate;
use Misaf\VendraUserProfile\Database\Factories\UserProfileFactory;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\Pages\CreateUserProfile;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\Pages\EditUserProfile;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\Pages\ViewUserProfile;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\UserProfileResource;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext([UserProfileResource::class]);
});

it('renders the create user profile page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    livewire(CreateUserProfile::class)
        ->assertOk();
});

it('renders the edit user profile page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $userProfile = UserProfileFactory::new()->createOne();

    livewire(EditUserProfile::class, ['record' => $userProfile->getKey()])
        ->assertOk();
});

it('renders the view user profile page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $userProfile = UserProfileFactory::new()->createOne();

    livewire(ViewUserProfile::class, ['record' => $userProfile->getKey()])
        ->assertOk();
});

it('lets authorization for another guard principal fall through safely', function (): void {
    Gate::define('foreign-principal-check', fn (): bool => false);

    expect(Gate::forUser(new class extends AuthenticatableUser
    {
        use HasFactory;
    })->allows('foreign-principal-check'))->toBeFalse();
});
