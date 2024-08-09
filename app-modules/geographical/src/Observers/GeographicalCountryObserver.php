<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Observers;

use App\Jobs\DeleteImageJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Termehsoft\Geographical\Models\GeographicalCountry;

#[ObservedBy([GeographicalCountryObserver::class])]
final class GeographicalCountryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the GeographicalCountry "deleted" event.
     *
     * @param GeographicalCountry $geographicalCountry
     * @return void
     */
    public function deleted(GeographicalCountry $geographicalCountry): void
    {
        DB::transaction(function () use ($geographicalCountry): void {
            $geographicalCountry->geographicalStates()->delete();
            $geographicalCountry->geographicalCities()->delete();
            $geographicalCountry->geographicalNeighborhoods()->delete();
        });

        $this->clearCaches($geographicalCountry);
    }

    /**
     * Handle the GeographicalCountry "deleting" event.
     *
     * @param GeographicalCountry $geographicalCountry
     * @return void
     */
    public function deleting(GeographicalCountry $geographicalCountry): void
    {
        if ($geographicalCountry->isForceDeleting()) {
            $geographicalCountry->geographicalStates()->each(function ($item): void {
                $item->geographicalCities()->each(function ($item2): void {
                    $item2->geographicalNeighborhoods()->each(function ($item3): void {
                        // DeleteImageJob::dispatchIf($item3->getRawOriginal('image') !== GeographicalNeighborhood::IMAGE_PATH, $item3);
                    });

                    // DeleteImageJob::dispatchIf($item2->getRawOriginal('image') !== GeographicalCity::IMAGE_PATH, $item2);
                });

                // DeleteImageJob::dispatchIf($item->getRawOriginal('image') !== GeographicalState::IMAGE_PATH, $item);
            });
        }
    }

    /**
     * Handle the GeographicalCountry "saved" event.
     *
     * @param GeographicalCountry $geographicalCountry
     * @return void
     */
    public function saved(GeographicalCountry $geographicalCountry): void
    {
        $this->clearCaches($geographicalCountry);
    }

    /**
     * Clear relevant caches.
     *
     * @param GeographicalCountry $geographicalCountry
     * @return void
     */
    private function clearCaches(GeographicalCountry $geographicalCountry): void
    {
        $this->forgetRowCountCache();
    }

    /**
     * Forget the geographical country row count cache.
     *
     * @return void
     */
    private function forgetRowCountCache(): void
    {
        Cache::forget('geographical-country-row-count');
    }
}
