<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\NewsletterSubscriberResource;

final class ViewNewsletterSubscriber extends ViewRecord
{
    protected static string $resource = NewsletterSubscriberResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-newsletter::navigation.newsletter_subscriber');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
