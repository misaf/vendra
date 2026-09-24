<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\RelationManagers;

use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class TransactionMetadataRelationManager extends RelationManager
{
    protected static string $relationship = 'transactionMetadatas';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedCodeBracket;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-transaction::navigation.transaction_metadata');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key_name')
                    ->label(__('vendra-transaction::attributes.key_name'))
                    ->maxLength(255)
                    ->required(),

                TextInput::make('key_value')
                    ->label(__('vendra-transaction::attributes.key_value'))
                    ->maxLength(255)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key_name')
                    ->label(__('vendra-transaction::attributes.key_name'))
                    ->searchable(),

                TextColumn::make('key_value')
                    ->label(__('vendra-transaction::attributes.key_value'))
                    ->searchable(),
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
}
