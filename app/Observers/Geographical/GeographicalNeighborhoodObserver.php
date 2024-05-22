<?php

declare(strict_types=1);

namespace App\Observers\Geographical;

use App\Models\Geographical\GeographicalNeighborhood;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class GeographicalNeighborhoodObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the GeographicalNeighborhood "created" event.
     *
     * @param GeographicalNeighborhood $geographicalNeighborhood
     * @return void
     */
    public function created(GeographicalNeighborhood $geographicalNeighborhood): void {}

    /**
     * Handle the GeographicalNeighborhood "deleted" event.
     *
     * @param GeographicalNeighborhood $geographicalNeighborhood
     * @return void
     */
    public function deleted(GeographicalNeighborhood $geographicalNeighborhood): void {}

    /**
     * Handle the GeographicalNeighborhood "force deleted" event.
     *
     * @param GeographicalNeighborhood $geographicalNeighborhood
     * @return void
     */
    public function forceDeleted(GeographicalNeighborhood $geographicalNeighborhood): void {}

    /**
     * Handle the GeographicalNeighborhood "restored" event.
     *
     * @param GeographicalNeighborhood $geographicalNeighborhood
     * @return void
     */
    public function restored(GeographicalNeighborhood $geographicalNeighborhood): void {}

    /**
     * Handle the GeographicalNeighborhood "updated" event.
     *
     * @param GeographicalNeighborhood $geographicalNeighborhood
     * @return void
     */
    public function updated(GeographicalNeighborhood $geographicalNeighborhood): void {}
}
