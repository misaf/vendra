<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories\Pages;

use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories\CustomPageCategoryResource;

final class CreateCustomPageCategory extends CreateRecord
{
    use Translatable;

    protected static string $resource = CustomPageCategoryResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/create-record.breadcrumb').' '.__('vendra-custom-page::navigation.custom_page_category');
    }

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }
}
