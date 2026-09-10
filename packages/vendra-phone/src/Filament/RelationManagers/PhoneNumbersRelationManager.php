<?php

declare(strict_types=1);

namespace Misaf\VendraPhone\Filament\RelationManagers;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Layout\Component as LayoutComponent;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraPhone\Models\PhoneNumber;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

final class PhoneNumbersRelationManager extends RelationManager
{
    protected static string $relationship = 'phoneNumbers';

    public static function getModelLabel(): string
    {
        return __('vendra-phone::phone.phone_number');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-phone::phone.phone_numbers');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-phone::phone.phone_numbers');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('type')->label(__('vendra-phone::phone.fields.type'))->required(),
            TextInput::make('label')->label(__('vendra-phone::phone.fields.label')),
            PhoneInput::make('number')
                ->label(__('vendra-phone::phone.fields.number'))
                ->countryStatePath('country_code')
                ->displayNumberFormat(PhoneInputNumberType::INTERNATIONAL)
                ->inputNumberFormat(PhoneInputNumberType::E164)
                ->strictMode()
                ->required(),
            TextInput::make('extension')->label(__('vendra-phone::phone.fields.extension')),
            KeyValue::make('metadata')
                ->label(__('vendra-phone::phone.fields.metadata'))
                ->columnSpanFull(),
            Textarea::make('notes')
                ->label(__('vendra-phone::phone.fields.notes'))
                ->columnSpanFull(),
            Toggle::make('is_primary')
                ->label(__('vendra-phone::phone.fields.is_primary'))
                ->onIcon(Heroicon::Bolt),
        ]);
    }

    public function table(Table $table): Table
    {
        /** @var array<int, Column|ColumnGroup|LayoutComponent> $columns */
        $columns = [
            BadgeableColumn::make('label')
                ->label(__('vendra-phone::phone.fields.label'))
                ->icon(Heroicon::Tag)
                ->default('—')
                ->prefixBadges([
                    Badge::make('is_primary')
                        ->label(__('vendra-phone::phone.fields.is_primary'))
                        ->color('success')
                        ->size(Size::ExtraSmall)
                        ->hidden(fn (PhoneNumber $record): bool => ! $record->is_primary),
                ]),
            TextColumn::make('type')
                ->label(__('vendra-phone::phone.fields.type'))
                ->icon(Heroicon::Tag)
                ->badge(),
            PhoneColumn::make('number')
                ->label(__('vendra-phone::phone.fields.number'))
                ->countryColumn('country_code')
                ->displayFormat(PhoneInputNumberType::INTERNATIONAL),
            TextColumn::make('extension')->label(__('vendra-phone::phone.fields.extension')),
            ToggleColumn::make('verified_at')
                ->disabled(fn (PhoneNumber $record): bool => ! (auth()->user()?->can('update', $record) ?? false))
                ->label(__('vendra-phone::phone.fields.verified_at'))
                ->onIcon(Heroicon::Bolt)
                ->state(fn (PhoneNumber $record): bool => $record->verified_at !== null)
                ->updateStateUsing(function (PhoneNumber $record, bool $state): bool {
                    $record->update(['verified_at' => $state ? now() : null]);

                    return $state;
                }),
        ];

        return $table
            ->columns($columns)
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
