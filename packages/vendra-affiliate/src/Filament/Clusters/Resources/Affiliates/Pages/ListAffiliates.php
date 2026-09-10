<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\AffiliateResource;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Widgets\AffiliateClicksOverview;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Widgets\AffiliateCommissionsOverview;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Widgets\AffiliateReferralsOverview;

final class ListAffiliates extends ListRecords
{
    protected static string $resource = AffiliateResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb').' '.__('vendra-affiliate::navigation.affiliate');
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
            AffiliateClicksOverview::class,
            AffiliateReferralsOverview::class,
            AffiliateCommissionsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
