<?php

declare(strict_types=1);

namespace Misaf\VendraAddress\Filament\RelationManagers;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraAddress\Models\Address;
use Misaf\VendraSupport\Capabilities\Countries;

final class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public static function getModelLabel(): string
    {
        return __('vendra-address::address.address');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-address::address.addresses');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-address::address.addresses');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('type')->label(__('vendra-address::address.fields.type'))->required(),
            TextInput::make('label')->label(__('vendra-address::address.fields.label')),
            TextInput::make('recipient_name')->label(__('vendra-address::address.fields.recipient_name')),
            TextInput::make('organization')->label(__('vendra-address::address.fields.organization')),
            TextInput::make('line_one')->label(__('vendra-address::address.fields.line_one'))->required(),
            TextInput::make('line_two')->label(__('vendra-address::address.fields.line_two')),
            TextInput::make('line_three')->label(__('vendra-address::address.fields.line_three')),
            TextInput::make('locality')->label(__('vendra-address::address.fields.locality')),
            TextInput::make('administrative_area')->label(__('vendra-address::address.fields.administrative_area')),
            TextInput::make('postal_code')->label(__('vendra-address::address.fields.postal_code')),
            TextInput::make('sorting_code')->label(__('vendra-address::address.fields.sorting_code')),
            Select::make('country_code')
                ->label(__('vendra-address::address.fields.country_code'))
                ->native(false)
                ->options(fn (): array => Countries::options())
                ->searchable()
                ->required(),
            TextInput::make('locale')->label(__('vendra-address::address.fields.locale')),
            KeyValue::make('metadata')
                ->label(__('vendra-address::address.fields.metadata'))
                ->columnSpanFull(),
            Textarea::make('notes')
                ->label(__('vendra-address::address.fields.notes'))
                ->columnSpanFull(),
            Toggle::make('is_primary')
                ->label(__('vendra-address::address.fields.is_primary'))
                ->onIcon(Heroicon::Bolt),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeableColumn::make('label')
                    ->label(__('vendra-address::address.fields.label'))
                    ->icon(Heroicon::Tag)
                    ->default('—')
                    ->prefixBadges([
                        Badge::make('is_primary')
                            ->label(__('vendra-address::address.fields.is_primary'))
                            ->color('success')
                            ->size(Size::ExtraSmall)
                            ->hidden(fn (Address $record): bool => ! $record->is_primary),
                    ]),
                TextColumn::make('line_one')->label(__('vendra-address::address.fields.line_one'))->icon(Heroicon::MapPin)->searchable(),
                TextColumn::make('locality')->label(__('vendra-address::address.fields.locality'))->icon(Heroicon::MapPin)->searchable(),
                TextColumn::make('country_code')->label(__('vendra-address::address.fields.country_code'))->icon(Heroicon::GlobeAlt)->badge(),
                ToggleColumn::make('verified_at')
                    ->disabled(fn (Address $record): bool => ! (auth()->user()?->can('update', $record) ?? false))
                    ->label(__('vendra-address::address.fields.verified_at'))
                    ->onIcon(Heroicon::Bolt)
                    ->state(fn (Address $record): bool => $record->verified_at !== null)
                    ->updateStateUsing(function (Address $record, bool $state): bool {
                        $record->update(['verified_at' => $state ? now() : null]);

                        return $state;
                    }),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
