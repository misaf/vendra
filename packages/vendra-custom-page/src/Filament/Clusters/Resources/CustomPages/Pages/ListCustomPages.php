<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\CustomPageResource;

final class ListCustomPages extends ListRecords
{
    use Translatable;

    protected static string $resource = CustomPageResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb').' '.__('vendra-custom-page::navigation.custom_page');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            LocaleSwitcher::make(),
        ];
    }
}
