<?php

declare(strict_types=1);

namespace App\Observers\Geographical;

use App\Jobs\DeleteImageJob;
use App\Models\Geographical\GeographicalCity;
use App\Models\Geographical\GeographicalNeighborhood;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

final class GeographicalStateObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Geographical\GeographicalState $geographicalState): void {}

    public function deleted(\App\Models\Geographical\GeographicalState $geographicalState): void
    {
        DB::transaction(function () use ($geographicalState): void {
            $geographicalState->geographicalCities()->delete();
            $geographicalState->geographicalNeighborhoods()->delete();
        });
    }

    public function deleting(\App\Models\Geographical\GeographicalState $geographicalState): void
    {
        if ($geographicalState->isForceDeleting()) {
            $geographicalState->geographicalCities()->each(function ($item): void {
                $item->geographicalNeighborhoods()->each(function ($item2): void {
                    // DeleteImageJob::dispatchIf($item2->getRawOriginal('image') !== GeographicalNeighborhood::IMAGE_PATH, $item2);
                });

                // DeleteImageJob::dispatchIf($item->getRawOriginal('image') !== GeographicalCity::IMAGE_PATH, $item);
            });
        }
    }

    public function forceDeleted(\App\Models\Geographical\GeographicalState $geographicalState): void {}

    public function restored(\App\Models\Geographical\GeographicalState $geographicalState): void {}

    public function updated(\App\Models\Geographical\GeographicalState $geographicalState): void {}
}
