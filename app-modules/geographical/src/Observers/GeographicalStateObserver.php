<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Observers;

use App\Jobs\DeleteImageJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Termehsoft\Geographical\Models\GeographicalState;

final class GeographicalStateObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the GeographicalState "created" event.
     *
     * @param GeographicalState $geographicalState
     */
    public function created(GeographicalState $geographicalState): void {}

    /**
     * Handle the GeographicalState "deleted" event.
     *
     * @param GeographicalState $geographicalState
     */
    public function deleted(GeographicalState $geographicalState): void
    {
        DB::transaction(function () use ($geographicalState): void {
            $geographicalState->geographicalCities()->delete();
            $geographicalState->geographicalNeighborhoods()->delete();
        });
    }

    /**
     * Handle the GeographicalState "deleting" event.
     *
     * @param GeographicalState $geographicalState
     */
    public function deleting(GeographicalState $geographicalState): void
    {
        if ($geographicalState->isForceDeleting()) {
            $geographicalState->geographicalCities()->each(function ($item): void {
                $item->geographicalNeighborhoods()->each(function ($item2): void {
                    // DeleteImageJob::dispatchIf($item2->getRawOriginal('image') !== GeographicalNeighborhood::IMAGE_PATH, $item2);
                });

                // DeleteImageJob::dispatchIf($item->getRawOriginal('image') !== GeographicalCity::IMAGE_PATH, $item);
            });
        }
    }

    /**
     * Handle the GeographicalState "force deleted" event.
     *
     * @param GeographicalState $geographicalState
     */
    public function forceDeleted(GeographicalState $geographicalState): void {}

    /**
     * Handle the GeographicalState "restored" event.
     *
     * @param GeographicalState $geographicalState
     */
    public function restored(GeographicalState $geographicalState): void {}

    /**
     * Handle the GeographicalState "updated" event.
     *
     * @param GeographicalState $geographicalState
     */
    public function updated(GeographicalState $geographicalState): void {}
}
