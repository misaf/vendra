<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\Widgets\NewsletterSubscriberOverviewWidget;

final class ListNewsletterSubscribers extends ListRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb').' '.__('vendra-newsletter::navigation.newsletter_subscriber');
    }

    /**
     * @return array<string, int>
     */
    public function getHeaderWidgetsColumns(): array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            NewsletterSubscriberOverviewWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
