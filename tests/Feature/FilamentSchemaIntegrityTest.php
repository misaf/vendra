<?php

declare(strict_types=1);

use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema as FilamentSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraAttribute\Database\Factories\AttributeFactory;
use Misaf\VendraAttribute\Database\Factories\AttributeValueFactory;
use Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\RelationManagers\AttributeValueRelationManager;
use Misaf\VendraCustomPage\Database\Factories\CustomPageCategoryFactory;
use Misaf\VendraCustomPage\Database\Factories\CustomPageFactory;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\RelationManagers\CustomPageRelationManager;
use Misaf\VendraFaq\Database\Factories\FaqCategoryFactory;
use Misaf\VendraFaq\Database\Factories\FaqFactory;
use Misaf\VendraFaq\Filament\Clusters\Resources\Faqs\RelationManagers\FaqRelationManager;
use Misaf\VendraPermission\Database\Factories\PermissionFactory;
use Misaf\VendraPermission\Database\Factories\RoleFactory;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\RelationManagers\PermissionRelationManager;
use Misaf\VendraPermission\Tests\Support\PermissionModuleTestContext;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Database\Factories\ProductPriceFactory;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices\RelationManagers\ProductPriceRelationManager;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\RelationManagers\ProductRelationManager;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraTransaction\Database\Factories\TransactionFactory;
use Misaf\VendraTransaction\Database\Factories\TransactionGatewayFactory;
use Misaf\VendraTransaction\Database\Factories\TransactionLimitFactory;
use Misaf\VendraTransaction\Database\Factories\WalletFactory;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Pages\EditTransactionGateway;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\RelationManagers\TransactionsRelationManager;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Schemas\TransactionForm;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\Pages\ViewWallet;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\RelationManagers\TransactionLimitsRelationManager;
use Misaf\VendraUser\Database\Factories\UserFactory;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Store::factory()->active()->create()->makeCurrent();
});

it('counts relation manager badges without preloaded relations', function (): void {
    $attribute = AttributeFactory::new()->createOne();
    AttributeValueFactory::new()->count(2)->forAttribute($attribute)->forAttributable($attribute)->create();

    $customPageCategory = CustomPageCategoryFactory::new()->createOne();
    CustomPageFactory::new()->count(3)->forCategory($customPageCategory)->create();

    $faqCategory = FaqCategoryFactory::new()->createOne();
    FaqFactory::new()->count(5)->forCategory($faqCategory)->create();

    $role = RoleFactory::new()->forGuard('web')->createOne();
    $permissions = PermissionFactory::new()->count(2)->forGuard('web')->create();
    $role->givePermissionTo($permissions);

    $productCategory = ProductCategoryFactory::new()->createOne();
    $products = ProductFactory::new()->count(3)->forCategory($productCategory)->create();
    ProductPriceFactory::new()->count(2)->forProduct($products->firstOrFail())->create();

    expect(AttributeValueRelationManager::getBadge($attribute, ''))->toBe('2')
        ->and(CustomPageRelationManager::getBadge($customPageCategory, ''))->toBe('3')
        ->and(FaqRelationManager::getBadge($faqCategory, ''))->toBe('5')
        ->and(PermissionRelationManager::getBadge($role, ''))->toBe('2')
        ->and(ProductRelationManager::getBadge($productCategory, ''))->toBe('3')
        ->and(ProductPriceRelationManager::getBadge($products->firstOrFail(), ''))->toBe('2');
});

it('enforces active form uniqueness with tenant-aware database indexes', function (): void {
    expect(indexNames('users'))->toContain('users_active_email_unique')
        ->and(indexNames('user_profiles'))->toContain(
            'user_profiles_active_name_unique',
            'user_profiles_active_slug_unique',
            'user_profiles_one_default_per_user_unique',
        )
        ->and(indexNames('attributes'))->toContain('attributes_active_name_unique')
        ->and(indexNames('currencies'))->toContain(
            'currencies_one_default_unique',
        );
});

it('builds one transaction limit tab per type from wallet transaction limits', function (): void {
    PermissionModuleTestContext::setUpFilamentAdminContext();

    $wallet = WalletFactory::new()->create();
    $otherWallet = WalletFactory::new()->create();

    TransactionLimitFactory::new()->forWallet($wallet)->ofType(TransactionTypeEnum::Deposit)->create();
    TransactionLimitFactory::new()->forWallet($wallet)->ofType(TransactionTypeEnum::Withdrawal)->create();
    TransactionLimitFactory::new()->forWallet($wallet)->ofType(TransactionTypeEnum::Commission)->create();
    TransactionLimitFactory::new()->forWallet($otherWallet)->ofType(TransactionTypeEnum::Bonus)->create();

    $tabs = livewire(TransactionLimitsRelationManager::class, [
        'ownerRecord' => $wallet,
        'pageClass' => ViewWallet::class,
    ])->instance()->getTabs();
    $badgeProperty = new ReflectionProperty(Tab::class, 'badge');

    foreach ($tabs as $tab) {
        expect($tab->isBadgeDeferred())->toBeTrue()
            ->and($badgeProperty->getValue($tab))->toBeInstanceOf(Closure::class);
    }

    expect(array_keys($tabs))->toBe([
        'all',
        TransactionTypeEnum::Deposit->value,
        TransactionTypeEnum::Withdrawal->value,
        TransactionTypeEnum::Commission->value,
        TransactionTypeEnum::Bonus->value,
        TransactionTypeEnum::Transfer->value,
    ])->and(Arr::get($tabs, 'all')->getBadge())->toBe('3')
        ->and($tabs[TransactionTypeEnum::Deposit->value]->getBadge())->toBe('1')
        ->and($tabs[TransactionTypeEnum::Withdrawal->value]->getBadge())->toBe('1')
        ->and($tabs[TransactionTypeEnum::Commission->value]->getBadge())->toBe('1')
        ->and($tabs[TransactionTypeEnum::Bonus->value]->getBadge())->toBe('0')
        ->and($tabs[TransactionTypeEnum::Transfer->value]->getBadge())->toBe('0')
        ->and(TransactionLimitsRelationManager::isBadgeDeferred($wallet, ViewWallet::class))->toBeTrue();
});

it('scopes gateway relation tabs to gateway transactions and keeps the user selectable', function (): void {
    PermissionModuleTestContext::setUpFilamentAdminContext();

    $gateway = TransactionGatewayFactory::new()->createOne();
    $user = UserFactory::new()->createOne();

    TransactionFactory::new()->count(2)->forGateway($gateway)->forUser($user)->deposit()->create();
    TransactionFactory::new()->forGateway($gateway)->forUser($user)->withdrawal()->create();

    $component = livewire(TransactionsRelationManager::class, [
        'ownerRecord' => $gateway,
        'pageClass' => EditTransactionGateway::class,
    ]);

    $tabs = $component->instance()->getTabs();
    $form = TransactionForm::configure(FilamentSchema::make($component->instance()));
    $badgeProperty = new ReflectionProperty(Tab::class, 'badge');

    foreach ($tabs as $tab) {
        expect($tab->isBadgeDeferred())->toBeTrue()
            ->and($badgeProperty->getValue($tab))->toBeInstanceOf(Closure::class);
    }

    expect(Arr::get($tabs, 'all')->getBadge())->toBe('3')
        ->and($tabs[TransactionTypeEnum::Deposit->value]->getBadge())->toBe('2')
        ->and($tabs[TransactionTypeEnum::Withdrawal->value]->getBadge())->toBe('1')
        ->and(TransactionsRelationManager::isBadgeDeferred($gateway, EditTransactionGateway::class))->toBeTrue()
        ->and($form->getFlatFields(withHidden: false))->toHaveKey('user_id');
});

/**
 * @return list<string>
 */
function indexNames(string $table): array
{
    return array_values(array_map(
        static fn (array $index): string => (string) Arr::get($index, 'name'),
        Schema::getIndexes($table),
    ));
}
