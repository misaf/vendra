<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Schemas;

use Filament\Schemas\Schema;
use Misaf\VendraMultimedia\Filament\Forms\Components\ModelImageUpload;
use Misaf\VendraSupport\Filament\Forms\Components\DescriptionTextarea;
use Misaf\VendraSupport\Filament\Forms\Components\IsActiveToggle;
use Misaf\VendraSupport\Filament\Forms\Components\IsDefaultToggle;
use Misaf\VendraSupport\Filament\Forms\Components\SluggableNameInput;
use Misaf\VendraSupport\Filament\Forms\Components\SlugInput;
use Misaf\VendraTransaction\Models\TransactionGateway;

final class TransactionGatewayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SluggableNameInput::make(),

                SlugInput::make()
                    ->extraInputAttributes(['dir' => 'ltr'])
                    ->uniqueWithinTenant(),

                DescriptionTextarea::make()
                    ->maxLength(1000)
                    ->rows(3),

                ModelImageUpload::make()
                    ->collection(TransactionGateway::MEDIA_COLLECTION)
                    ->panelLayout(null)
                    ->responsiveImages(false),

                IsActiveToggle::make()
                    ->default(false),

                IsDefaultToggle::make()
                    ->helperText(__('vendra-transaction::attributes.is_default_helper_text')),
            ]);
    }
}
