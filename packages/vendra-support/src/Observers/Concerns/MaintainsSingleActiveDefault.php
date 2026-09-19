<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Observers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Keep exactly one active row flagged `is_default` for an observed model with
 * `active` and `is_default` columns and an `active` scope.
 *
 * The first active row becomes the default, an inactive row never is, and when
 * the default goes away the first active row by the model's `ordered` scope, or
 * by key, takes over. A soft-deleted default gives up the flag, so restoring it
 * does not take the default back.
 */
trait MaintainsSingleActiveDefault
{
    public function creating(Model $model): void
    {
        if (! $model->getAttribute('active')) {
            $model->setAttribute('is_default', false);

            return;
        }

        if (! $this->activeRows($model)->exists()) {
            $model->setAttribute('is_default', true);
        }
    }

    public function saving(Model $model): void
    {
        if (! $model->getAttribute('active')) {
            $model->setAttribute('is_default', false);

            return;
        }

        if ($model->getAttribute('is_default')) {
            $model->newQuery()
                ->where('is_default', true)
                ->whereKeyNot($model->getKey())
                ->update(['is_default' => false]);

            return;
        }

        if ($model->exists && $model->getOriginal('is_default') === true) {
            $hasAnotherDefault = $this->activeRows($model)
                ->where('is_default', true)
                ->whereKeyNot($model->getKey())
                ->exists();

            if (! $hasAnotherDefault) {
                $model->setAttribute('is_default', true);
            }
        }
    }

    public function saved(Model $model): void
    {
        if ($model->wasChanged(['active', 'is_default'])) {
            $this->ensureActiveDefault($model);
        }
    }

    public function deleted(Model $model): void
    {
        if (! $model->getAttribute('is_default')) {
            return;
        }

        if ($model->exists) {
            $model->forceFill(['is_default' => false])->saveQuietly();
        }

        $this->ensureActiveDefault($model);
    }

    private function ensureActiveDefault(Model $model): void
    {
        if ($this->activeRows($model)->where('is_default', true)->exists()) {
            return;
        }

        $fallback = $this->activeRows($model);

        $model->hasNamedScope('ordered')
            ? $fallback->scopes(['ordered'])
            : $fallback->orderBy($model->getKeyName());

        $fallback->first()?->update(['is_default' => true]);
    }

    /**
     * @return Builder<Model>
     */
    private function activeRows(Model $model): Builder
    {
        return $model->newQuery()->scopes(['active']);
    }
}
