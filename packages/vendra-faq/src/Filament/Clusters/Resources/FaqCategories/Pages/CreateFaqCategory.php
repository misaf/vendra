<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\Pages;

use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;
use Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\FaqCategoryResource;

final class CreateFaqCategory extends CreateRecord
{
    use Translatable;

    protected static string $resource = FaqCategoryResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/create-record.breadcrumb').' '.__('vendra-faq::navigation.faq_category');
    }

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }
}
