<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories\CustomPageCategoryResource;

final class ViewCustomPageCategory extends ViewRecord
{
    use Translatable;

    protected static string $resource = CustomPageCategoryResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-custom-page::navigation.custom_page_category');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            LocaleSwitcher::make(),
        ];
    }
}
