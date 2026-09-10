<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraTransaction\Database\Factories\TransactionFactory;
use Misaf\VendraTransaction\Database\Factories\TransactionGatewayFactory;
use Misaf\VendraTransaction\Database\Factories\WalletFactory;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Pages\CreateTransactionGateway;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Pages\EditTransactionGateway;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Pages\ListTransactionGateways;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Pages\ViewTransactionGateway;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Pages\CreateTransaction;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Pages\ListTransactions;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Pages\ViewTransaction;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\TransactionResource;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\Pages\ListWallets;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\Pages\ViewWallet;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();

    Filament::getPanel('admin')->plugin(
        SpatieTranslatablePlugin::make()->defaultLocales(['en', 'de']),
    );
});

it('renders the transaction pages under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $transaction = TransactionFactory::new()->deposit()->create();

    livewire(ListTransactions::class)->assertOk();
    livewire(CreateTransaction::class)->assertOk();
    livewire(ViewTransaction::class, ['record' => $transaction->getKey()])->assertOk();
});

it('renders the transaction gateway pages under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $gateway = TransactionGatewayFactory::new()->create();

    livewire(ListTransactionGateways::class)->assertOk();
    livewire(CreateTransactionGateway::class)->assertOk();
    livewire(ViewTransactionGateway::class, ['record' => $gateway->getKey()])->assertOk();
    livewire(EditTransactionGateway::class, ['record' => $gateway->getKey()])->assertOk();
});

it('renders the wallet pages under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $wallet = WalletFactory::new()->create();

    livewire(ListWallets::class)->assertOk();
    livewire(ViewWallet::class, ['record' => $wallet->getKey()])->assertOk();
});

it('globally searches transactions by related users inside the current tenant', function (): void {
    $tenant = currentTestTenant();
    $user = createTestUser([
        'username' => 'transaction-search-user',
        'email' => 'transaction-search-user@example.test',
    ]);
    $transaction = TransactionFactory::new()->forUser($user)->deposit()->createOne();

    $otherTenant = createTestTenant();
    Filament::setTenant($otherTenant);
    switchToTestTenant($otherTenant);
    $otherUser = createTestUser([
        'username' => 'other-transaction-user',
        'email' => 'other-transaction-user@example.test',
    ]);
    TransactionFactory::new()->forUser($otherUser)->deposit()->createOne();
    Filament::setTenant($tenant);
    switchToTestTenant($tenant);

    $result = TransactionResource::getGlobalSearchResults('transaction-search-user@example.test')->sole();
    $loadedTransaction = TransactionResource::getGlobalSearchEloquentQuery()
        ->findOrFail($transaction->getKey());

    expect(TransactionResource::getGloballySearchableAttributes())->toBe([
        'token',
        'wallet.user.username',
        'wallet.user.email',
    ])
        ->and($result->title)->toBe($transaction->token)
        ->and($result->details)->toBe([
            __('vendra-user::attributes.email') => 'transaction-search-user@example.test',
        ])
        ->and($loadedTransaction->relationLoaded('wallet'))->toBeTrue()
        ->and($loadedTransaction->wallet?->relationLoaded('user'))->toBeTrue()
        ->and(TransactionResource::getGlobalSearchResults('other-transaction-user'))->toBeEmpty();
});
