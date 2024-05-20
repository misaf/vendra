<?php

declare(strict_types=1);

namespace App\Observers\Geographical;

use App\Jobs\DeleteImageJob;
use App\Models\Geographical\GeographicalNeighborhood;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class GeographicalCityObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Geographical\GeographicalCity $geographicalCity): void {}

    public function deleted(\App\Models\Geographical\GeographicalCity $geographicalCity): void
    {
        $geographicalCity->geographicalNeighborhoods()->delete();
    }

    public function deleting(\App\Models\Geographical\GeographicalCity $geographicalCity): void
    {
        if ($geographicalCity->isForceDeleting()) {
            $geographicalCity->geographicalNeighborhoods()->each(function ($item): void {
                // DeleteImageJob::dispatchIf($item->getRawOriginal('image') !== GeographicalNeighborhood::IMAGE_PATH, $item);
            });
        }
    }

    public function forceDeleted(\App\Models\Geographical\GeographicalCity $geographicalCity): void {}

    public function restored(\App\Models\Geographical\GeographicalCity $geographicalCity): void {}

    public function updated(\App\Models\Geographical\GeographicalCity $geographicalCity): void {}
}
