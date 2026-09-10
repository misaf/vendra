<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Pages;

use Filament\Resources\Pages\ListRecords;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\AffiliateCommissionResource;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Widgets\AffiliateCommissionOverviewWidget;

final class ListAffiliateCommissions extends ListRecords
{
    protected static string $resource = AffiliateCommissionResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb').' '.__('vendra-affiliate::navigation.affiliate_commission');
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
            AffiliateCommissionOverviewWidget::class,
        ];
    }
}
