<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use LaraZeus\SpatieTranslatable\Resources\RelationManagers\Concerns\Translatable;
use Livewire\Attributes\Reactive;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\CustomPageResource;
use Misaf\VendraCustomPage\Models\CustomPageCategory;

final class CustomPageRelationManager extends RelationManager
{
    use Translatable;

    #[Reactive]
    public ?string $activeLocale = null;

    protected static string $relationship = 'customPages';

    protected static bool $isBadgeDeferred = true;

    protected static bool $isLazy = false;

    public static function getModelLabel(): string
    {
        return __('vendra-custom-page::navigation.custom_page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-custom-page::navigation.custom_pages');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-custom-page::navigation.custom_pages');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        if (! $ownerRecord instanceof CustomPageCategory) {
            return (string) Number::format(0);
        }

        return (string) Number::format($ownerRecord->customPages()->count());
    }

    public function form(Schema $schema): Schema
    {
        return CustomPageResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return CustomPageResource::table($table)
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
