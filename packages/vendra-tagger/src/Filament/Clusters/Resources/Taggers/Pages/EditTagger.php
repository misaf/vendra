<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;
use Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\TaggerResource;

final class EditTagger extends EditRecord
{
    use Translatable;

    protected static string $resource = TaggerResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/edit-record.breadcrumb').' '.__('vendra-tagger::navigation.tagger');
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
