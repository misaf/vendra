<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\Pages;

use Filament\Resources\Pages\ViewRecord;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\AffiliatePayoutResource;

final class ViewAffiliatePayout extends ViewRecord
{
    protected static string $resource = AffiliatePayoutResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-affiliate::navigation.affiliate_payout');
    }
}
