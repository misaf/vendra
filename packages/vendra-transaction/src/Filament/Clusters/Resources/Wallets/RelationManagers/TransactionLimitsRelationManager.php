<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\RelationManagers;

use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;
use Illuminate\Validation\Rules\Unique;
use LogicException;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Models\TransactionLimit;
use Misaf\VendraTransaction\Models\Wallet;

final class TransactionLimitsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactionLimits';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedScale;

    protected static bool $isBadgeDeferred = true;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-transaction::navigation.transaction_limits');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        $limitCount = $ownerRecord instanceof Wallet
            ? $ownerRecord->transactionLimits()->count()
            : 0;

        return (string) Number::format($limitCount);
    }

    /**
     * @return array<string|int, Tab>
     */
    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make()
                ->badge(fn (): string => (string) Number::format($this->transactionLimits()->count()))
                ->deferBadge(),
        ];

        foreach (TransactionTypeEnum::cases() as $type) {
            $tabs[$type->value] = Tab::make()
                ->badge(fn (): string => (string) Number::format($this->transactionLimits()->ofType($type)->count()))
                ->deferBadge()
                ->label($type->getLabel())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('transaction_type', $type));
        }

        return $tabs;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('transaction_type')
                    ->label(__('vendra-transaction::attributes.transaction_type'))
                    ->native(false)
                    ->options(TransactionTypeEnum::class)
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule): Unique => $rule->where('wallet_id', $this->wallet()->id)),

                TextInput::make('amount')
                    ->extraInputAttributes(['dir' => 'ltr'])
                    ->inputMode('numeric')
                    ->integer()
                    ->label(__('vendra-transaction::attributes.amount'))
                    ->minValue(1)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_type')
                    ->badge()
                    ->label(__('vendra-transaction::attributes.transaction_type'))
                    ->icon(Heroicon::Tag),

                TextColumn::make('amount')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-transaction::attributes.amount'))
                    ->numeric()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),

                DeleteAction::make(),
            ])
            ->defaultSort(column: 'id', direction: 'desc');
    }

    /**
     * @return HasMany<TransactionLimit, Wallet>
     */
    private function transactionLimits(): HasMany
    {
        return $this->wallet()->transactionLimits();
    }

    private function wallet(): Wallet
    {
        $wallet = $this->getOwnerRecord();

        throw_unless($wallet instanceof Wallet, LogicException::class, 'Transaction limits are listed only for a wallet.');

        return $wallet;
    }
}
