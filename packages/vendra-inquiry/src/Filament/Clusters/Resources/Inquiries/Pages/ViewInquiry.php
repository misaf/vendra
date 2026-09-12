<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Actions\AnswerInquiryTableAction;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Actions\CloseInquiryTableAction;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Actions\ReopenInquiryTableAction;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\InquiryResource;

final class ViewInquiry extends ViewRecord
{
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            AnswerInquiryTableAction::make(),
            CloseInquiryTableAction::make(),
            ReopenInquiryTableAction::make(),
            DeleteAction::make(),
        ];
    }
}
