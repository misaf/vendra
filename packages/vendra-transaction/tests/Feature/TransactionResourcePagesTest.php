<?php

declare(strict_types=1);

use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Arr;
use Misaf\VendraSupport\Capabilities\CurrencyIntegration;
use Misaf\VendraTransaction\Actions\PostLedgerEntryAction;
use Misaf\VendraTransaction\Database\Factories\TransactionFactory;
use Misaf\VendraTransaction\Database\Factories\TransactionGatewayFactory;
use Misaf\VendraTransaction\Database\Factories\WalletFactory;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Pages\CreateTransaction;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Pages\ListTransactions;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Pages\ViewTransaction;
use Misaf\VendraTransaction\Models\Wallet;
use Misaf\VendraTransaction\States\Approved;
use Misaf\VendraTransaction\States\Declined;
use Misaf\VendraTransaction\Support\TransactionUsers;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();
});

it('lists transactions in the table', function (): void {
    $transactions = TransactionFactory::new()->deposit()->count(3)->create();

    livewire(ListTransactions::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords($transactions);
});

it('defers transaction filter tab badge queries', function (): void {
    TransactionFactory::new()->deposit()->count(2)->create();
    TransactionFactory::new()->withdrawal()->create();

    $tabs = livewire(ListTransactions::class)->instance()->getTabs();
    $badgeProperty = new ReflectionProperty(Tab::class, 'badge');

    foreach ($tabs as $tab) {
        expect($tab->isBadgeDeferred())->toBeTrue()
            ->and($badgeProperty->getValue($tab))->toBeInstanceOf(Closure::class);
    }

    expect(Arr::get($tabs, 'all')->getBadge())->toBe('3')
        ->and($tabs[TransactionTypeEnum::Deposit->value]->getBadge())->toBe('2')
        ->and($tabs[TransactionTypeEnum::Withdrawal->value]->getBadge())->toBe('1')
        ->and($tabs[TransactionTypeEnum::Commission->value]->getBadge())->toBe('0')
        ->and($tabs[TransactionTypeEnum::Transfer->value]->getBadge())->toBe('0')
        ->and($tabs[TransactionTypeEnum::Bonus->value]->getBadge())->toBe('0');
});

it('provisions the wallet from the selected user and currency on create', function (): void {
    $gateway = TransactionGatewayFactory::new()->active()->create();
    $currencyCode = CurrencyIntegration::defaultCode();
    $user = TransactionUsers::model()::factory()->create();

    expect(Wallet::query()->where('user_id', $user->getKey())->exists())->toBeFalse();

    livewire(CreateTransaction::class)
        ->fillForm([
            'user_id' => $user->getKey(),
            'currency_code' => $currencyCode,
            'transaction_gateway_id' => $gateway->id,
            'transaction_type' => TransactionTypeEnum::Deposit->value,
            'amount' => 5_000,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $wallet = Wallet::query()->where('user_id', $user->getKey())->where('currency_code', $currencyCode)->sole();

    expect($wallet->balance)->toBe(0)
        ->and($wallet->transactions()->deposit()->pending()->count())->toBe(1);
});

it('provisions both wallets for a transfer created from the form', function (): void {
    $gateway = TransactionGatewayFactory::new()->active()->create();
    $currencyCode = CurrencyIntegration::defaultCode();
    $source = TransactionUsers::model()::factory()->create();
    $destination = TransactionUsers::model()::factory()->create();

    livewire(CreateTransaction::class)
        ->fillForm([
            'user_id' => $source->getKey(),
            'currency_code' => $currencyCode,
            'transaction_gateway_id' => $gateway->id,
            'transaction_type' => TransactionTypeEnum::Transfer->value,
            'counterparty_user_id' => $destination->getKey(),
            'amount' => 1_000,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $sourceWallet = Wallet::query()->where('user_id', $source->getKey())->sole();
    $destinationWallet = Wallet::query()->where('user_id', $destination->getKey())->sole();

    expect($sourceWallet->transactions()->transfer()->sole()->counterparty_wallet_id)->toBe($destinationWallet->id)
        ->and($destinationWallet->currency_code)->toBe($currencyCode);
});

it('approves a transaction from the view page and settles the ledger', function (): void {
    $wallet = WalletFactory::new()->create();
    $transaction = TransactionFactory::new()->forWallet($wallet)->deposit()->create(['amount' => 2_000]);

    livewire(ViewTransaction::class, ['record' => $transaction->getKey()])
        ->callAction('approve')
        ->assertNotified();

    expect($transaction->fresh()->status)->toBeInstanceOf(Approved::class)
        ->and($wallet->fresh()->balance)->toBe(2_000);
});

it('declines a transaction from the view page without touching the ledger', function (): void {
    $wallet = WalletFactory::new()->create();
    $transaction = TransactionFactory::new()->forWallet($wallet)->deposit()->create(['amount' => 2_000]);

    livewire(ViewTransaction::class, ['record' => $transaction->getKey()])
        ->callAction('decline')
        ->assertNotified();

    expect($transaction->fresh()->status)->toBeInstanceOf(Declined::class)
        ->and($wallet->fresh()->balance)->toBe(0)
        ->and($wallet->ledgerEntries()->count())->toBe(0);
});

it('notifies instead of settling when the balance is insufficient', function (): void {
    $wallet = WalletFactory::new()->create();
    resolve(PostLedgerEntryAction::class)->execute($wallet, 500);

    $transaction = TransactionFactory::new()->forWallet($wallet)->withdrawal()->create(['amount' => 2_000]);

    livewire(ViewTransaction::class, ['record' => $transaction->getKey()])
        ->callAction('approve')
        ->assertNotified();

    expect($transaction->fresh()->status->isFinal())->toBeFalse()
        ->and($wallet->fresh()->balance)->toBe(500);
});
