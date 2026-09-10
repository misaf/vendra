<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Pages;

use Filament\Resources\Pages\ViewRecord;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\AffiliateCommissionResource;

final class ViewAffiliateCommission extends ViewRecord
{
    protected static string $resource = AffiliateCommissionResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-affiliate::navigation.affiliate_commission');
    }
}
