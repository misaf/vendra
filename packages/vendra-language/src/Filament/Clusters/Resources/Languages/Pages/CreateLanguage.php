<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Pages;

use Filament\Resources\Pages\CreateRecord;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\LanguageResource;

final class CreateLanguage extends CreateRecord
{
    protected static string $resource = LanguageResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/create-record.breadcrumb').' '.__('vendra-language::navigation.language');
    }
}
