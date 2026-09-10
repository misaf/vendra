<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Pages;

use Filament\Resources\Pages\CreateRecord;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\AffiliateResource;

final class CreateAffiliate extends CreateRecord
{
    protected static string $resource = AffiliateResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/create-record.breadcrumb').' '.__('vendra-affiliate::navigation.affiliate');
    }
}
