<?php

declare(strict_types=1);

namespace Misaf\VendraVerification\Filament\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Capabilities\Countries;

final class VerificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'verifications';

    public static function getModelLabel(): string
    {
        return __('vendra-verification::verification.verification');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-verification::verification.verifications');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-verification::verification.verifications');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('type')->label(__('vendra-verification::verification.fields.type'))->required(),
            Select::make('status')
                ->label(__('vendra-verification::verification.fields.status'))
                ->options([
                    'pending' => __('vendra-verification::verification.statuses.pending'),
                    'approved' => __('vendra-verification::verification.statuses.approved'),
                    'rejected' => __('vendra-verification::verification.statuses.rejected'),
                    'expired' => __('vendra-verification::verification.statuses.expired'),
                ])
                ->required(),
            TextInput::make('provider')->label(__('vendra-verification::verification.fields.provider')),
            TextInput::make('reference')->label(__('vendra-verification::verification.fields.reference')),
            Select::make('country_code')
                ->label(__('vendra-verification::verification.fields.country_code'))
                ->native(false)
                ->options(fn (): array => Countries::options())
                ->searchable(),
            DateTimePicker::make('verified_at')->label(__('vendra-verification::verification.fields.verified_at')),
            DateTimePicker::make('expires_at')
                ->label(__('vendra-verification::verification.fields.expires_at'))
                ->after('verified_at'),
            KeyValue::make('metadata')
                ->label(__('vendra-verification::verification.fields.metadata'))
                ->columnSpanFull(),
            Textarea::make('notes')
                ->label(__('vendra-verification::verification.fields.notes'))
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->label(__('vendra-verification::verification.fields.type'))->icon(Heroicon::Tag)->badge(),
                TextColumn::make('status')->label(__('vendra-verification::verification.fields.status'))->badge(),
                TextColumn::make('provider')->label(__('vendra-verification::verification.fields.provider')),
                TextColumn::make('reference')
                    ->label(__('vendra-verification::verification.fields.reference'))
                    ->copyable(),
                TextColumn::make('country_code')
                    ->label(__('vendra-verification::verification.fields.country_code'))
                    ->icon(Heroicon::GlobeAlt)
                    ->badge(),
                TextColumn::make('verified_at')
                    ->alignCenter()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-verification::verification.fields.verified_at'))
                    ->sinceTooltip()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                    ),
                TextColumn::make('expires_at')
                    ->alignCenter()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-verification::verification.fields.expires_at'))
                    ->sinceTooltip()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                    ),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
