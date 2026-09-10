<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Pages;

use Filament\Resources\Pages\CreateRecord;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\NewsletterResource;

final class CreateNewsletter extends CreateRecord
{
    protected static string $resource = NewsletterResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/create-record.breadcrumb').' '.__('vendra-newsletter::navigation.newsletter');
    }
}
