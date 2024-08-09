<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Termehsoft\Geographical\Models\GeographicalNeighborhood;

#[ObservedBy([GeographicalNeighborhoodObserver::class])]
final class GeographicalNeighborhoodObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the GeographicalNeighborhood "deleted" event.
     *
     * @param GeographicalNeighborhood $geographicalNeighborhood
     * @return void
     */
    public function deleted(GeographicalNeighborhood $geographicalNeighborhood): void
    {
        $this->clearCaches($geographicalNeighborhood);
    }

    /**
     * Handle the GeographicalNeighborhood "saved" event.
     *
     * @param GeographicalNeighborhood $geographicalNeighborhood
     * @return void
     */
    public function saved(GeographicalNeighborhood $geographicalNeighborhood): void
    {
        $this->clearCaches($geographicalNeighborhood);
    }

    /**
     * Clear relevant caches.
     *
     * @param GeographicalNeighborhood $geographicalNeighborhood
     * @return void
     */
    private function clearCaches(GeographicalNeighborhood $geographicalNeighborhood): void
    {
        $this->forgetRowCountCache();
    }

    /**
     * Forget the geographical neighborhood row count cache.
     *
     * @return void
     */
    private function forgetRowCountCache(): void
    {
        Cache::forget('geographical-neighborhood-row-count');
    }
}
