<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;
use Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\TaggerResource;

final class ViewTagger extends ViewRecord
{
    use Translatable;

    protected static string $resource = TaggerResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-tagger::navigation.tagger');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            LocaleSwitcher::make(),
        ];
    }
}
