<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Observers;

use App\Jobs\DeleteImageJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Termehsoft\Geographical\Models\GeographicalCity;

final class GeographicalCityObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the GeographicalCity "created" event.
     *
     * @param GeographicalCity $geographicalCity
     */
    public function created(GeographicalCity $geographicalCity): void {}

    /**
     * Handle the GeographicalCity "deleted" event.
     *
     * @param GeographicalCity $geographicalCity
     */
    public function deleted(GeographicalCity $geographicalCity): void
    {
        $geographicalCity->geographicalNeighborhoods()->delete();
    }

    /**
     * Handle the GeographicalCity "deleting" event.
     *
     * @param GeographicalCity $geographicalCity
     */
    public function deleting(GeographicalCity $geographicalCity): void
    {
        if ($geographicalCity->isForceDeleting()) {
            $geographicalCity->geographicalNeighborhoods()->each(function ($item): void {
                // DeleteImageJob::dispatchIf($item->getRawOriginal('image') !== GeographicalNeighborhood::IMAGE_PATH, $item);
            });
        }
    }

    /**
     * Handle the GeographicalCity "force deleted" event.
     *
     * @param GeographicalCity $geographicalCity
     */
    public function forceDeleted(GeographicalCity $geographicalCity): void {}

    /**
     * Handle the GeographicalCity "restored" event.
     *
     * @param GeographicalCity $geographicalCity
     */
    public function restored(GeographicalCity $geographicalCity): void {}

    /**
     * Handle the GeographicalCity "updated" event.
     *
     * @param GeographicalCity $geographicalCity
     */
    public function updated(GeographicalCity $geographicalCity): void {}
}
