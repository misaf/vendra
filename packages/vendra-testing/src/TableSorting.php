<?php

declare(strict_types=1);

namespace Misaf\VendraTesting;

use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportTesting\Testable;

final class TableSorting
{
    /**
     * Sorts every sortable column of the page's table in both directions and
     * asserts the rendered record order matches the persisted values. Records
     * must be created so each one sorts before the next on every sortable
     * column (for example ascending names, amounts, and creation order).
     *
     * Set `$assertDescendingOrder` to false for tables whose grouping pins the
     * visible record order regardless of the sort direction; the descending
     * pass then only asserts the records remain visible.
     *
     * @param  array<int, Model>  $recordsInAscendingOrder
     */
    public static function assertSortsByEverySortableColumn(
        Testable $listPage,
        array $recordsInAscendingOrder,
        bool $assertDescendingOrder = true,
    ): void {
        foreach ($listPage->instance()->getTable()->getColumns() as $column) {
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
