<?php

declare(strict_types=1);

namespace Misaf\VendraConsole\Filament\Resources\Plans\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Misaf\VendraConsole\Filament\Resources\Plans\Actions\DeletePlanPageAction;
use Misaf\VendraConsole\Filament\Resources\Plans\PlanResource;
use Misaf\VendraConsole\Filament\Resources\Plans\Schemas\PlanForm;
use Misaf\VendraSubscription\Actions\UpdatePlanAction;
use Misaf\VendraSubscription\Models\Plan;

final class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeletePlanPageAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return PlanForm::normalizeLimits($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        throw_unless($record instanceof Plan, InvalidArgumentException::class, 'Plan pages require a Plan record.');

        return resolve(UpdatePlanAction::class)->execute($record, $data);
    }
}
