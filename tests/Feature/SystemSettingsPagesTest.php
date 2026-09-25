<?php

declare(strict_types=1);

use App\Filament\Admin\Pages\ManageGeneralSettings;
use App\Settings\SettingsScope;
use Illuminate\Support\Facades\DB;
use Misaf\VendraAffiliate\Filament\Pages\ManageAffiliateSettings;
use Misaf\VendraCart\Filament\Pages\ManageCartSettings;
use Misaf\VendraDelivery\Filament\Pages\ManageDeliverySettings;
use Misaf\VendraInquiry\Filament\Pages\ManageInquirySettings;
use Misaf\VendraOrder\Filament\Pages\ManageOrderSettings;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraSupport\Filament\Clusters\SystemCluster;
use Misaf\VendraWishlist\Filament\Pages\ManageWishlistSettings;

use function Pest\Livewire\livewire;

/**
 * @return array<string, string>
 */
function settingsPayloads(string $group, string $scope): array
{
    return DB::table('settings')
        ->where('group', $group)
        ->where('scope', $scope)
        ->orderBy('name')
        ->pluck('payload', 'name')
        ->all();
}

it('saves each store settings page into the store scope without touching the platform defaults', function (string $page): void {
    $store = setUpFilamentAdminTestContext();
    $group = $page::getSettings()::group();
    $platformDefaults = settingsPayloads($group, SettingsScope::PLATFORM);

    livewire($page)
        ->assertOk()
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect($page::getCluster())->toBe(SystemCluster::class)
        ->and($platformDefaults)->not->toBeEmpty()
        ->and(settingsPayloads($group, SettingsScope::PLATFORM))->toBe($platformDefaults)
        ->and(array_keys(settingsPayloads($group, SettingsScope::forTenant($store->getKey()))))->toBe(array_keys($platformDefaults));
})->with([
    'general' => ManageGeneralSettings::class,
    'orders' => ManageOrderSettings::class,
    'carts' => ManageCartSettings::class,
    'delivery' => ManageDeliverySettings::class,
    'affiliates' => ManageAffiliateSettings::class,
    'enquiries' => ManageInquirySettings::class,
    'wishlists' => ManageWishlistSettings::class,
]);

it('numbers orders with the prefix the store saved and leaves other stores on the default', function (): void {
    setUpFilamentAdminTestContext();

    livewire(ManageOrderSettings::class)
        ->fillForm(['number_prefix' => 'FLW'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Order::generateNumber())->toStartWith('FLW-');

    Store::factory()->active()->create()->makeCurrent();

    expect(Order::generateNumber())->toStartWith('ORD-');
});

it('rejects an occasion that is not a slug', function (): void {
    setUpFilamentAdminTestContext();

    livewire(ManageInquirySettings::class)
        ->fillForm(['occasions' => ['wedding', 'birth day']])
        ->call('save')
        ->assertHasFormErrors(['occasions.1']);
});
