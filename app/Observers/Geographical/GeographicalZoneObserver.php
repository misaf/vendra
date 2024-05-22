<?php

declare(strict_types=1);

namespace App\Observers\Geographical;

use App\Jobs\DeleteImageJob;
use App\Models\Geographical\GeographicalCity;
use App\Models\Geographical\GeographicalCountry;
use App\Models\Geographical\GeographicalNeighborhood;
use App\Models\Geographical\GeographicalState;
use App\Models\Geographical\GeographicalZone;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

final class GeographicalZoneObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the GeographicalZone "created" event.
     *
     * @param GeographicalZone $geographicalZone
     * @return void
     */
    public function created(GeographicalZone $geographicalZone): void {}

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
     * Handle the GeographicalZone "force deleted" event.
     *
     * @param GeographicalZone $geographicalZone
     * @return void
     */
    public function forceDeleted(GeographicalZone $geographicalZone): void {}

    /**
     * Handle the GeographicalZone "restored" event.
     *
     * @param GeographicalZone $geographicalZone
     * @return void
     */
    public function restored(GeographicalZone $geographicalZone): void {}

    /**
     * Handle the GeographicalZone "updated" event.
     *
     * @param GeographicalZone $geographicalZone
     * @return void
     */
    public function updated(GeographicalZone $geographicalZone): void {}
}
