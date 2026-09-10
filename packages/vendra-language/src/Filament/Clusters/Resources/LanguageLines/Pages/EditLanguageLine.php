<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\LanguageLineResource;

final class EditLanguageLine extends EditRecord
{
    protected static string $resource = LanguageLineResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/edit-record.breadcrumb').' '.__('vendra-language::navigation.language');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            DeleteAction::make(),
        ];
    }
}
