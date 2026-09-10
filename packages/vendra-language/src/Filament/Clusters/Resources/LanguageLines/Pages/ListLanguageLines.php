<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Misaf\VendraLanguage\Filament\Actions\SyncLanguageLinesPageAction;
use Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\LanguageLineResource;

final class ListLanguageLines extends ListRecords
{
    protected static string $resource = LanguageLineResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb').' '.__('vendra-language::navigation.language');
    }

    protected function getHeaderActions(): array
    {
        return [
            SyncLanguageLinesPageAction::make(),

            CreateAction::make(),
        ];
    }
}
