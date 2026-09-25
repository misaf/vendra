<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\RelationManagers;

use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraStore\Filament\Actions\AddDomainAliasTableAction;
use Misaf\VendraStore\Filament\Actions\RemoveDomainAliasTableAction;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\DeletedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsActiveIconColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsPrimaryIconColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\NameColumn;

/**
 * A store's domains. The primary domain, the one the store was created with, is
 * fixed; aliases are added from the header and soft-deleted per row, and removed
 * ones stay listed as trashed history.
 *
 * Panels supply their own alias actions, which carry the panel's access rules.
 */
abstract class DomainsRelationManager extends RelationManager
{
    protected static string $relationship = 'domains';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedGlobeAlt;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-store::attributes.domains');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                NameColumn::make()
                    ->label(__('vendra-store::attributes.domain'))
                    ->icon(Heroicon::GlobeAlt)
                    ->searchable(),

                IsActiveIconColumn::make(),

                IsPrimaryIconColumn::make(),

                CreatedAtColumn::make()
                    ->sortable(),

                DeletedAtColumn::make()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([$this->addDomainAliasAction()])
            ->recordActions([$this->removeDomainAliasAction()])
            ->defaultSort('id', 'desc');
    }

    abstract protected function addDomainAliasAction(): AddDomainAliasTableAction;

    abstract protected function removeDomainAliasAction(): RemoveDomainAliasTableAction;
}
