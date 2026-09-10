<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\CustomPageResource;

final class ViewCustomPage extends ViewRecord
{
    use Translatable;

    protected static string $resource = CustomPageResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-custom-page::navigation.custom_page');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            LocaleSwitcher::make(),
        ];
    }
}
