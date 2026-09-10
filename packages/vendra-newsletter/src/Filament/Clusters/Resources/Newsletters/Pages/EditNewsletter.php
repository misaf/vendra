<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Actions\SendNewsletterTableAction;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\NewsletterResource;

final class EditNewsletter extends EditRecord
{
    protected static string $resource = NewsletterResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/edit-record.breadcrumb').' '.__('vendra-newsletter::navigation.newsletter');
    }

    protected function getHeaderActions(): array
    {
        return [
            SendNewsletterTableAction::make(),

            ViewAction::make(),

            DeleteAction::make(),
        ];
    }
}
