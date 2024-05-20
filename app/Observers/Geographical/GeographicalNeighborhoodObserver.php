<?php

declare(strict_types=1);

namespace App\Observers\Geographical;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class GeographicalNeighborhoodObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Geographical\GeographicalNeighborhood $geographicalNeighborhood): void {}

    public function deleted(\App\Models\Geographical\GeographicalNeighborhood $geographicalNeighborhood): void {}

    public function forceDeleted(\App\Models\Geographical\GeographicalNeighborhood $geographicalNeighborhood): void {}

    public function restored(\App\Models\Geographical\GeographicalNeighborhood $geographicalNeighborhood): void {}

    public function updated(\App\Models\Geographical\GeographicalNeighborhood $geographicalNeighborhood): void {}
}
