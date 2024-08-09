<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Observers;

use App\Jobs\DeleteImageJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Termehsoft\Geographical\Models\GeographicalZone;

#[ObservedBy([GeographicalZoneObserver::class])]
final class GeographicalZoneObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the GeographicalZone "deleted" event.
     *
     * @param GeographicalZone $geographicalZone
     * @return void
     */
    public function deleted(GeographicalZone $geographicalZone): void
    {
        DB::transaction(function () use ($geographicalZone): void {
            $geographicalZone->geographicalCountries()->delete();
            $geographicalZone->geographicalStates()->delete();
            $geographicalZone->geographicalCities()->delete();
            $geographicalZone->geographicalNeighborhoods()->delete();
        });

        $this->clearCaches($geographicalZone);
    }

    /**
     * Handle the GeographicalZone "deleting" event.
     *
     * @param GeographicalZone $geographicalZone
     * @return void
     */
    public function deleting(GeographicalZone $geographicalZone): void
    {
        if ($geographicalZone->isForceDeleting()) {
            $geographicalZone->geographicalCountries()->each(function ($item): void {
                $item->geographicalStates()->each(function ($item2): void {
                    $item2->geographicalCities()->each(function ($item3): void {
                        $item3->geographicalNeighborhoods()->each(function ($item4): void {
                            // DeleteImageJob::dispatchIf($item4->getRawOriginal('image') !== GeographicalNeighborhood::IMAGE_PATH, $item4);
                        });

                        // DeleteImageJob::dispatchIf($item3->getRawOriginal('image') !== GeographicalCity::IMAGE_PATH, $item3);
                    });

                    // DeleteImageJob::dispatchIf($item2->getRawOriginal('image') !== GeographicalState::IMAGE_PATH, $item2);
                });

                // DeleteImageJob::dispatchIf($item->getRawOriginal('image') !== GeographicalCountry::IMAGE_PATH, $item);
            });
        }
    }

    /**
     * Handle the GeographicalZone "saved" event.
     *
     * @param GeographicalZone $geographicalZone
     * @return void
     */
    public function saved(GeographicalZone $geographicalZone): void
    {
        $this->clearCaches($geographicalZone);
    }

    /**
     * Clear relevant caches.
     *
     * @param GeographicalZone $geographicalZone
     * @return void
     */
    private function clearCaches(GeographicalZone $geographicalZone): void
    {
        $this->forgetRowCountCache();
    }

    /**
     * Forget the geographical zone row count cache.
     *
     * @return void
     */
    private function forgetRowCountCache(): void
    {
        Cache::forget('geographical-zone-row-count');
    }
}
