<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Misaf\VendraOrder\Database\Factories\OrderFactory;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\OrderResource;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Pages\ListOrders;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Pages\ViewOrder;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();
});

it('renders the orders table under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $order = OrderFactory::new()->createOne();

    livewire(ListOrders::class)
        ->assertOk()
        ->call('loadTable')
        ->assertCanSeeTableRecords([$order]);
});

it('renders the view order page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $order = OrderFactory::new()->createOne();

    livewire(ViewOrder::class, ['record' => $order->getKey()])
        ->assertOk();
});

it('globally searches order references with customer context inside the current tenant', function (): void {
    $tenant = currentTestTenant();
    $customer = createTestUser([
        'username' => 'search-customer',
        'email' => 'search-customer@example.test',
    ]);
    $order = OrderFactory::new()->forCustomer($customer)->createOne([
        'number' => 'ORD-GLOBAL-SEARCH',
        'payment_reference' => 'PAY-GLOBAL-REFERENCE',
    ]);

    $otherTenant = createTestTenant();
    Filament::setTenant($otherTenant);
    switchToTestTenant($otherTenant);
    OrderFactory::new()->createOne(['number' => 'ORD-OTHER-TENANT']);
    Filament::setTenant($tenant);
    switchToTestTenant($tenant);

    $result = OrderResource::getGlobalSearchResults('PAY-GLOBAL-REFERENCE')->sole();
    $loadedOrder = OrderResource::getGlobalSearchEloquentQuery()->findOrFail($order->getKey());

    expect(OrderResource::getGloballySearchableAttributes())->toBe([
        'number',
        'payment_reference',
    ])
        ->and($result->title)->toBe('ORD-GLOBAL-SEARCH')
        ->and($result->details)->toBe([
            __('vendra-order::attributes.customer') => 'search-customer',
            __('vendra-order::attributes.status') => $order->status->getLabel(),
        ])
        ->and($loadedOrder->relationLoaded('customer'))->toBeTrue()
        ->and(OrderResource::getGlobalSearchResults('ORD-OTHER-TENANT'))->toBeEmpty();
});
