<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Termehsoft\Geographical\Models\GeographicalNeighborhood;

final class GeographicalNeighborhoodObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the GeographicalNeighborhood "created" event.
     *
     * @param GeographicalNeighborhood $geographicalNeighborhood
     */
    public function created(GeographicalNeighborhood $geographicalNeighborhood): void {}

    /**
     * Handle the GeographicalNeighborhood "deleted" event.
     *
     * @param GeographicalNeighborhood $geographicalNeighborhood
     */
    public function deleted(GeographicalNeighborhood $geographicalNeighborhood): void {}

    /**
     * Handle the GeographicalNeighborhood "force deleted" event.
     *
     * @param GeographicalNeighborhood $geographicalNeighborhood
     */
    public function forceDeleted(GeographicalNeighborhood $geographicalNeighborhood): void {}

    /**
     * Handle the GeographicalNeighborhood "restored" event.
     *
     * @param GeographicalNeighborhood $geographicalNeighborhood
     */
    public function restored(GeographicalNeighborhood $geographicalNeighborhood): void {}

    /**
     * Handle the GeographicalNeighborhood "updated" event.
     *
     * @param GeographicalNeighborhood $geographicalNeighborhood
     */
    public function updated(GeographicalNeighborhood $geographicalNeighborhood): void {}
}
