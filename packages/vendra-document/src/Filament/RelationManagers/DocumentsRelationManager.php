<?php

declare(strict_types=1);

namespace Misaf\VendraDocument\Filament\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraDocument\Models\Document;
use Misaf\VendraSupport\Capabilities\Countries;

final class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public static function getModelLabel(): string
    {
        return __('vendra-document::document.document');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-document::document.documents');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-document::document.documents');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('type')->label(__('vendra-document::document.fields.type'))->required(),
            Select::make('issuing_country_code')
                ->label(__('vendra-document::document.fields.issuing_country_code'))
                ->native(false)
                ->options(fn (): array => Countries::options())
                ->searchable(),
            TextInput::make('number')->label(__('vendra-document::document.fields.number')),
            SpatieMediaLibraryFileUpload::make('file')
                ->collection(Document::MEDIA_COLLECTION)
                ->disk('local')
                ->downloadable()
                ->label(__('vendra-document::document.fields.file'))
                ->openable()
                ->preserveFilenames()
                ->required()
                ->visibility('private')
                ->columnSpanFull(),
            DatePicker::make('issued_at')->label(__('vendra-document::document.fields.issued_at')),
            DatePicker::make('expires_at')
                ->label(__('vendra-document::document.fields.expires_at'))
                ->afterOrEqual('issued_at'),
            KeyValue::make('metadata')
                ->label(__('vendra-document::document.fields.metadata'))
                ->columnSpanFull(),
            Textarea::make('notes')
                ->label(__('vendra-document::document.fields.notes'))
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->label(__('vendra-document::document.fields.type'))->icon(Heroicon::Tag)->badge(),
                TextColumn::make('number')->label(__('vendra-document::document.fields.number'))->copyable(),
                TextColumn::make('issuing_country_code')
                    ->label(__('vendra-document::document.fields.issuing_country_code'))
                    ->icon(Heroicon::MapPin)
                    ->badge(),
                TextColumn::make('expires_at')->label(__('vendra-document::document.fields.expires_at'))->date(),
                ToggleColumn::make('verified_at')
                    ->disabled(fn (Document $record): bool => ! (auth()->user()?->can('update', $record) ?? false))
                    ->label(__('vendra-document::document.fields.verified_at'))
                    ->onIcon(Heroicon::Bolt)
                    ->state(fn (Document $record): bool => $record->verified_at !== null)
                    ->updateStateUsing(function (Document $record, bool $state): bool {
                        $record->update(['verified_at' => $state ? now() : null]);

                        return $state;
                    }),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
