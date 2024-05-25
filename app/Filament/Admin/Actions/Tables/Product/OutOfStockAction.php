<?php

declare(strict_types=1);

namespace App\Filament\Admin\Actions\Tables\Product;

use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

final class OutOfStockAction extends BulkAction
{
    use CanCustomizeProcess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('form.out_of_stock'));

        $this->successNotificationTitle(__('filament-actions::edit.single.notifications.saved.title'));

        $this->color('gray');

        $this->icon('heroicon-o-archive-box-x-mark');

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-archive-box-x-mark');

        $this->action(function (): void {
            $this->process(static fn(Collection $records) => $records->each(fn(Model $record) => $record->update(['in_stock' => false])));

            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): ?string
    {
        return 'outOfStock';
    }
}
