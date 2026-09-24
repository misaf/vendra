<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Observers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Keep exactly one row per owner carrying a boolean flag such as `is_default`
 * or `is_primary`, for an observed model owned through a foreign key column.
 *
 * Synchronous: `creating` and `saving` adjust the flag before the write. An
 * owner's first row takes the flag, flagging another row clears the rest, the
 * only flagged row cannot be unflagged, and when it is deleted the oldest
 * remaining row takes over. A soft-deleted row gives up the flag, so restoring
 * it does not take the flag back.
 */
trait MaintainsSingleFlagPerOwner
{
    abstract protected function flagColumn(): string;

    abstract protected function ownerColumn(): string;

    public function creating(Model $model): void
    {
        if (! $this->siblings($model)->exists()) {
            $model->setAttribute($this->flagColumn(), true);
        }
    }

    public function saving(Model $model): void
    {
        $flag = $this->flagColumn();

        if ($model->getAttribute($flag)) {
            $this->siblings($model)
                ->where($flag, true)
                ->update([$flag => false]);

            return;
        }

        if ($model->exists && $model->getOriginal($flag) === true
            && ! $this->siblings($model)->where($flag, true)->exists()) {
            $model->setAttribute($flag, true);
        }
    }

    public function deleted(Model $model): void
    {
        $flag = $this->flagColumn();

        if (! $model->getAttribute($flag)) {
            return;
        }

        if ($model->exists) {
            $model->forceFill([$flag => false])->saveQuietly();
        }

        $this->siblings($model)->orderBy($model->getKeyName())->first()?->update([$flag => true]);
    }

    /**
     * @return Builder<Model>
     */
    private function siblings(Model $model): Builder
    {
        return $model->newQuery()
            ->where($this->ownerColumn(), $model->getAttribute($this->ownerColumn()))
            ->when($model->exists, fn (Builder $query): Builder => $query->whereKeyNot($model->getKey()));
    }
}
