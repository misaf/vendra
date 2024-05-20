<?php

declare(strict_types=1);

namespace App\Observers\Geographical;

use App\Jobs\DeleteImageJob;
use App\Models\Geographical\GeographicalCity;
use App\Models\Geographical\GeographicalNeighborhood;
use App\Models\Geographical\GeographicalState;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

final class GeographicalCountryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Geographical\GeographicalCountry $geographicalCountry): void {}

    public function deleted(\App\Models\Geographical\GeographicalCountry $geographicalCountry): void
    {
        DB::transaction(function () use ($geographicalCountry): void {
            $geographicalCountry->geographicalStates()->delete();
            $geographicalCountry->geographicalCities()->delete();
            $geographicalCountry->geographicalNeighborhoods()->delete();
        });
    }

    public function deleting(\App\Models\Geographical\GeographicalCountry $geographicalCountry): void
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

    public function forceDeleted(\App\Models\Geographical\GeographicalCountry $geographicalCountry): void {}

    public function restored(\App\Models\Geographical\GeographicalCountry $geographicalCountry): void {}

    public function updated(\App\Models\Geographical\GeographicalCountry $geographicalCountry): void {}
}
