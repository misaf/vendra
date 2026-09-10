<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Illuminate\Validation\Rules\Unique;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

final class AttributeValueRelationManager extends RelationManager
{
    protected static string $relationship = 'values';

    protected static bool $isBadgeDeferred = true;

    protected static bool $isLazy = false;

    public static function getModelLabel(): string
    {
        return __('vendra-attribute::navigation.attribute_value');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-attribute::navigation.attribute_values');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-attribute::navigation.attribute_values');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        if (! $ownerRecord instanceof Attribute) {
            return (string) Number::format(0);
        }

        return (string) Number::format($ownerRecord->values()->count());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('value')
                    ->label(__('vendra-attribute::attributes.value'))
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule, ?Model $record): Unique => TenantAwareness::constrainUniqueRule($rule)
                            ->where('attribute_id', $record?->getAttribute('attribute_id'))
                            ->where('attributable_type', $record?->getAttribute('attributable_type'))
                            ->where('attributable_id', $record?->getAttribute('attributable_id'))
                            ->withoutTrashed(),
                    ),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('value')
                    ->label(__('vendra-attribute::attributes.value'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('position')
                    ->label(__('vendra-attribute::attributes.position'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),

                DeleteAction::make(),
            ])
            ->defaultSort('position', 'desc')
            ->reorderable('position', direction: 'desc');
    }
}
