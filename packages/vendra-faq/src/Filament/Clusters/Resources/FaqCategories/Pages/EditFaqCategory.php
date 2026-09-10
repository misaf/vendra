<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;
use Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\FaqCategoryResource;

final class EditFaqCategory extends EditRecord
{
    use Translatable;

    protected static string $resource = FaqCategoryResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/edit-record.breadcrumb').' '.__('vendra-faq::navigation.faq_category');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            DeleteAction::make(),

            LocaleSwitcher::make(),
        ];
    }
}
