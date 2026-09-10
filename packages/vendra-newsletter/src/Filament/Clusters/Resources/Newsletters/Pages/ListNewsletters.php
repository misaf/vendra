<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\NewsletterResource;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Widgets\NewsletterOverviewWidget;

final class ListNewsletters extends ListRecords
{
    protected static string $resource = NewsletterResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb').' '.__('vendra-newsletter::navigation.newsletter');
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
            NewsletterOverviewWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
