<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Schemas;

use Filament\Schemas\Schema;
use Misaf\VendraMultimedia\Filament\Infolists\Components\ModelImageEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\DescriptionEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\IsActiveEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\IsDefaultEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\NameEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\SlugEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;
use Misaf\VendraTransaction\Models\TransactionGateway;

final class TransactionGatewayInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                NameEntry::make(),
                SlugEntry::make(),
                IsActiveEntry::make(),
                IsDefaultEntry::make(),
                DescriptionEntry::make(),
                ModelImageEntry::make()
                    ->collection(TransactionGateway::MEDIA_COLLECTION),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
