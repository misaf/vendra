<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;
use Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\FaqCategoryResource;

final class ListFaqCategories extends ListRecords
{
    use Translatable;

    protected static string $resource = FaqCategoryResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb').' '.__('vendra-faq::navigation.faq_category');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            LocaleSwitcher::make(),
        ];
    }
}
