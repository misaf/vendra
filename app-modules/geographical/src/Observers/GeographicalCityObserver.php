<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Observers;

use App\Jobs\DeleteImageJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Termehsoft\Geographical\Models\GeographicalCity;

#[ObservedBy([GeographicalCityObserver::class])]
final class GeographicalCityObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the GeographicalCity "deleted" event.
     *
     * @param GeographicalCity $geographicalCity
     * @return void
     */
    public function deleted(GeographicalCity $geographicalCity): void
    {
        $geographicalCity->geographicalNeighborhoods()->delete();

        $this->clearCaches($geographicalCity);
    }

    /**
     * Handle the GeographicalCity "deleting" event.
     *
     * @param GeographicalCity $geographicalCity
     * @return void
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
     * Handle the GeographicalCity "saved" event.
     *
     * @param GeographicalCity $geographicalCity
     * @return void
     */
    public function saved(GeographicalCity $geographicalCity): void
    {
        $this->clearCaches($geographicalCity);
    }

    /**
     * Clear relevant caches.
     *
     * @param GeographicalCity $geographicalCity
     * @return void
     */
    private function clearCaches(GeographicalCity $geographicalCity): void
    {
        $this->forgetRowCountCache();
    }

    /**
     * Forget the geographical state row count cache.
     *
     * @return void
     */
    private function forgetRowCountCache(): void
    {
        Cache::forget('geographical-city-row-count');
    }
}
