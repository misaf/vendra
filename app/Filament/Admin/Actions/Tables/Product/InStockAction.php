<?php

declare(strict_types=1);

namespace App\Filament\Admin\Actions\Tables\Product;

use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

final class InStockAction extends BulkAction
{
    use CanCustomizeProcess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('form.in_stock'));

        $this->successNotificationTitle(__('filament-actions::edit.single.notifications.saved.title'));

        $this->color('primary');

        $this->icon('heroicon-o-archive-box-arrow-down');

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-archive-box-arrow-down');

        $this->action(function (): void {
            $this->process(static fn(Collection $records) => $records->each(fn(Model $record) => $record->update(['in_stock' => true])));

            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): ?string
    {
        return 'inStock';
    }
}
