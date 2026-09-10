<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Actions\ProcessPayoutAction;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\AffiliateResource;

final class ViewAffiliate extends ViewRecord
{
    protected static string $resource = AffiliateResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-affiliate::navigation.affiliate');
    }

    protected function getHeaderActions(): array
    {
        return [
            ProcessPayoutAction::make(),

            EditAction::make(),
        ];
    }
}
