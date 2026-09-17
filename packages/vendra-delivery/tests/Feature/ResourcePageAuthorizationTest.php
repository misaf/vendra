<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraDelivery\Database\Factories\DeliveryFactory;
use Misaf\VendraDelivery\Database\Factories\DeliverySlotFactory;
use Misaf\VendraDelivery\Database\Factories\DeliveryZoneFactory;
use Misaf\VendraDelivery\Filament\Clusters\Resources\Deliveries\Pages\ListDeliveries;
use Misaf\VendraDelivery\Filament\Clusters\Resources\Deliveries\Pages\ViewDelivery;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Pages\CreateDeliverySlot;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Pages\ListDeliverySlots;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\Pages\CreateDeliveryZone;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\Pages\EditDeliveryZone;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\Pages\ListDeliveryZones;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();

    Filament::getPanel('admin')->plugin(
        SpatieTranslatablePlugin::make()->defaultLocales(['en', 'de']),
    );

    Filament::getPanel('admin')->strictAuthorization();
});

it('renders the delivery zones table', function (): void {
    $zone = DeliveryZoneFactory::new()->createOne();

    livewire(ListDeliveryZones::class)
        ->assertOk()
        ->call('loadTable')
        ->assertCanSeeTableRecords([$zone]);
});

it('renders the create delivery zone page', function (): void {
    livewire(CreateDeliveryZone::class)->assertOk();
});

it('renders the edit delivery zone page', function (): void {
    $zone = DeliveryZoneFactory::new()->createOne();

    livewire(EditDeliveryZone::class, ['record' => $zone->getKey()])->assertOk();
});

it('renders the delivery windows table', function (): void {
    $slot = DeliverySlotFactory::new()->createOne();

    livewire(ListDeliverySlots::class)
        ->assertOk()
        ->call('loadTable')
        ->assertCanSeeTableRecords([$slot]);
});

it('renders the create delivery window page', function (): void {
    livewire(CreateDeliverySlot::class)->assertOk();
});

it('renders the deliveries table and view page', function (): void {
    $delivery = DeliveryFactory::new()->createOne();

    livewire(ListDeliveries::class)
        ->assertOk()
        ->call('loadTable')
        ->assertCanSeeTableRecords([$delivery]);

    livewire(ViewDelivery::class, ['record' => $delivery->getKey()])->assertOk();
});

it('toggles a delivery zone quote flag and active state from the table', function (): void {
    $zone = DeliveryZoneFactory::new()->createOne();

    livewire(ListDeliveryZones::class)
        ->call('loadTable')
        ->call('updateTableColumnState', 'requires_quote', (string) $zone->getKey(), true)
        ->call('updateTableColumnState', 'active', (string) $zone->getKey(), false);

    expect($zone->refresh()->requires_quote)->toBeTrue()
        ->and($zone->active)->toBeFalse();
});

it('toggles a delivery window active state from the table', function (): void {
    $slot = DeliverySlotFactory::new()->createOne(['active' => true]);

    livewire(ListDeliverySlots::class)
        ->call('loadTable')
        ->call('updateTableColumnState', 'active', (string) $slot->getKey(), false);

    expect($slot->refresh()->active)->toBeFalse();
});

it('shows whether a delivery was quoted by hand as an icon', function (): void {
    $delivery = DeliveryFactory::new()->createOne(['requires_quote' => true]);

    livewire(ListDeliveries::class)
        ->call('loadTable')
        ->assertTableColumnStateSet('requires_quote', true, $delivery);

    livewire(ViewDelivery::class, ['record' => $delivery->getKey()])
        ->assertSchemaStateSet(['requires_quote' => true]);
});
