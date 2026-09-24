<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Console\Commands;

use ArrayObject;
use Illuminate\Bus\UniqueLock;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\Events\UniqueJobSkipped;
use Illuminate\Support\Facades\Event;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontRuntimeConfiguration;
use Throwable;

/**
 * Subclasses choose the rows and the job; chunking, locking, and reporting live here.
 */
abstract class StorefrontDeploymentDispatchCommand extends Command
{
    /**
     * The current run's skipped deployment ids; null between runs.
     *
     * @var ArrayObject<int, int>|null
     */
    private ?ArrayObject $skipped = null;

    private bool $listensForSkippedDispatches = false;

    public function handle(StorefrontRuntimeConfiguration $runtime): int
    {
        if (! $runtime->isConfigured()) {
            $this->error($runtime->misconfigurationMessage());

            return self::FAILURE;
        }

        $count = 0;
        $outcomes = [];
        $failures = [];
        $skipped = $this->recordSkippedDispatches();

        $this->query()
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function (Collection $deployments) use (&$count, &$outcomes, &$failures, $skipped): void {
                foreach ($deployments as $deployment) {
                    if ($this->option('sync')) {
                        $this->runSync($deployment->id, $outcomes, $failures, $skipped);
                    } else {
                        $job = $this->jobFor($deployment->id);

                        if ($this->option('force-unique')) {
                            $this->releaseUniqueLock($job);
                        }

                        dispatch($job);
                    }

                    $count++;
                }
            });

        $this->skipped = null;

        $this->info(sprintf($this->summary(), $count - $skipped->count() - count($failures), $this->option('sync') ? $this->syncVerb() : $this->queuedVerb()));
        $this->reportSkipped($skipped);
        $this->reportOutcomes(array_values(array_filter($outcomes)));

        foreach ($failures as $deploymentId => $message) {
            $this->error(sprintf('Deployment [%d] failed: %s', $deploymentId, $message));
        }

        return $failures === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Run one deployment in this process under the queued job's unique lock.
     *
     * A failure is recorded and the pass moves on.
     *
     * @param  list<mixed>  $outcomes
     * @param  array<int, string>  $failures
     * @param  ArrayObject<int, int>  $skipped
     */
    private function runSync(int $deploymentId, array &$outcomes, array &$failures, ArrayObject $skipped): void
    {
        $job = $this->jobFor($deploymentId);
        $lock = new UniqueLock($this->laravel->make(Cache::class));

        if ($this->option('force-unique')) {
            $lock->release($job);
        }

        if (! $lock->acquire($job)) {
            $skipped->append($deploymentId);

            return;
        }

        try {
            $outcomes[] = $this->performSync($deploymentId);
        } catch (Throwable $exception) {
            report($exception);
            $failures[$deploymentId] = $exception->getMessage();
        } finally {
            $lock->release($job);
        }
    }

    /**
     * Release a job's unique lock so this dispatch is accepted.
     *
     * Only for a worker killed mid-provision, which holds its lock for the full
     * `uniqueFor` window. Using it while a job is running provisions twice.
     */
    private function releaseUniqueLock(object $job): void
    {
        new UniqueLock($this->laravel->make(Cache::class))->release($job);
    }

    /**
     * Collect the deployments whose unique job was discarded as a duplicate.
     *
     * Listening for the discard event is race-free, unlike checking the lock
     * first. The listener is registered once because Artisan reuses the command
     * instance.
     *
     * @return ArrayObject<int, int>
     */
    private function recordSkippedDispatches(): ArrayObject
    {
        /** @var ArrayObject<int, int> $skipped */
        $skipped = new ArrayObject;
        $this->skipped = $skipped;

        if (! $this->listensForSkippedDispatches) {
            Event::listen(function (UniqueJobSkipped $event): void {
                if ($this->skipped instanceof ArrayObject && ($event->job instanceof ProvisionStorefrontJob || $event->job instanceof ReconcileStorefrontJob)) {
                    $this->skipped->append($event->job->deploymentId);
                }
            });

            $this->listensForSkippedDispatches = true;
        }

        return $skipped;
    }

    /**
     * @param  ArrayObject<int, int>  $skipped
     */
    private function reportSkipped(ArrayObject $skipped): void
    {
        if ($skipped->count() === 0) {
            return;
        }

        $ids = $skipped->getArrayCopy();

        $slugs = StorefrontDeployment::query()
            ->whereIn('id', $ids)
            ->orderBy('slug')
            ->pluck('slug')
            ->implode(', ');

        $this->warn(sprintf(
            '%d skipped, already being provisioned: %s. A deployment holds its lock through every retry, so one that '
            .'is failing and backing off cannot be pushed again until the queue gives up on it.',
            count($ids),
            $slugs === '' ? implode(', ', $ids) : $slugs,
        ));
    }

    /**
     * @return Builder<StorefrontDeployment>
     */
    abstract protected function query(): Builder;

    /**
     * Get the `sprintf` summary template, taking the count and the verb.
     */
    abstract protected function summary(): string;

    abstract protected function syncVerb(): string;

    abstract protected function queuedVerb(): string;

    abstract protected function jobFor(int $deploymentId): object;

    /**
     * Run one deployment in this process and return its reportable outcome.
     *
     * `dispatch_sync()` returns the queue driver's result, not the handler's, so
     * a command that needs the outcome overrides this.
     */
    protected function performSync(int $deploymentId): mixed
    {
        return dispatch_sync($this->jobFor($deploymentId));
    }

    /**
     * Report what a `--sync` pass did; queued jobs have no outcome yet.
     *
     * @param  list<mixed>  $outcomes
     */
    protected function reportOutcomes(array $outcomes): void {}
}
