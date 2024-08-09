<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Observers;

use App\Jobs\DeleteImageJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Termehsoft\Geographical\Models\GeographicalState;

#[ObservedBy([GeographicalStateObserver::class])]
final class GeographicalStateObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the GeographicalState "deleted" event.
     *
     * @param GeographicalState $geographicalState
     * @return void
     */
    public function deleted(GeographicalState $geographicalState): void
    {
        DB::transaction(function () use ($geographicalState): void {
            $geographicalState->geographicalCities()->delete();
            $geographicalState->geographicalNeighborhoods()->delete();
        });

        $this->clearCaches($geographicalState);
    }

    /**
     * Handle the GeographicalState "deleting" event.
     *
     * @param GeographicalState $geographicalState
     * @return void
     */
    public function deleting(GeographicalState $geographicalState): void
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

    /**
     * Handle the GeographicalState "saved" event.
     *
     * @param GeographicalState $geographicalState
     * @return void
     */
    public function saved(GeographicalState $geographicalState): void
    {
        $this->clearCaches($geographicalState);
    }

    /**
     * Clear relevant caches.
     *
     * @param GeographicalState $geographicalState
     * @return void
     */
    private function clearCaches(GeographicalState $geographicalState): void
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
        Cache::forget('geographical-state-row-count');
    }
}
