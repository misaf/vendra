<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Misaf\VendraUser\Filament\Pages\Auth\EditProfile;

it('uses the vendra-user profile page on every panel', function (string $panel): void {
    expect(Filament::getPanel($panel)->getProfilePage())->toBe(EditProfile::class);
})->with(['admin', 'console', 'reseller']);
