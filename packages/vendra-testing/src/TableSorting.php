<?php

declare(strict_types=1);

namespace Misaf\VendraTesting;

use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use PHPUnit\Framework\Assert;

final class TableSorting
{
    /**
     * Assert that every sortable column sorts the records in both directions.
     *
     * The records must already be in ascending order on every sortable column.
     * Pass `$assertDescendingOrder` as false for tables whose grouping fixes the order.
     *
     * @param  Testable<Component>  $listPage
     * @param  array<int, Model>  $recordsInAscendingOrder
     */
    public static function assertSortsByEverySortableColumn(
        Testable $listPage,
        array $recordsInAscendingOrder,
        bool $assertDescendingOrder = true,
    ): void {
        $component = $listPage->instance();

        if (! $component instanceof HasTable) {
            Assert::fail('The list page must render a Filament table.');
        }

        foreach ($component->getTable()->getColumns() as $column) {
            if (! $column->isSortable()) {
                continue;
            }

            $listPage
                ->sortTable($column->getName())
                ->assertOk()
                ->assertCanSeeTableRecords($recordsInAscendingOrder, inOrder: true)
                ->sortTable($column->getName(), 'desc')
                ->assertOk();

            if ($assertDescendingOrder) {
                $listPage->assertCanSeeTableRecords(array_reverse($recordsInAscendingOrder), inOrder: true);
            } else {
                $listPage->assertCanSeeTableRecords($recordsInAscendingOrder);
            }
        }
    }
}
