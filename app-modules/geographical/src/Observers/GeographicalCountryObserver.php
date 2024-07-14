<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Observers;

use App\Jobs\DeleteImageJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Termehsoft\Geographical\Models\GeographicalCountry;

final class GeographicalCountryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the GeographicalCountry "created" event.
     *
     * @param GeographicalCountry $geographicalCountry
     */
    public function created(GeographicalCountry $geographicalCountry): void {}

    /**
     * Handle the GeographicalCountry "deleted" event.
     *
     * @param GeographicalCountry $geographicalCountry
     */
    public function deleted(GeographicalCountry $geographicalCountry): void
    {
        DB::transaction(function () use ($geographicalCountry): void {
            $geographicalCountry->geographicalStates()->delete();
            $geographicalCountry->geographicalCities()->delete();
            $geographicalCountry->geographicalNeighborhoods()->delete();
        });
    }

    /**
     * Handle the GeographicalCountry "deleting" event.
     *
     * @param GeographicalCountry $geographicalCountry
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
     * Handle the GeographicalCountry "force deleted" event.
     *
     * @param GeographicalCountry $geographicalCountry
     */
    public function forceDeleted(GeographicalCountry $geographicalCountry): void {}

    /**
     * Handle the GeographicalCountry "restored" event.
     *
     * @param GeographicalCountry $geographicalCountry
     */
    public function restored(GeographicalCountry $geographicalCountry): void {}

    /**
     * Handle the GeographicalCountry "updated" event.
     *
     * @param GeographicalCountry $geographicalCountry
     */
    public function updated(GeographicalCountry $geographicalCountry): void {}
}
