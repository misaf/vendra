<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Misaf\VendraStore\Jobs\RecordStorefrontRuntimeSnapshotJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Throwable;

final class StorefrontRuntimeSnapshots
{
    public const int TTL_SECONDS = 30;

    public function latest(StorefrontDeployment $deployment, bool $logs = false): ?StorefrontRuntimeSnapshot
    {
        return $this->centrally(function () use ($deployment, $logs): ?StorefrontRuntimeSnapshot {
            $deployment = StorefrontDeployment::query()->find($deployment->id);

            if ($deployment === null) {
                return null;
            }

            $key = $this->key($deployment, $logs);
            $cached = Cache::get($key);

            if (is_array($cached)) {
                try {
                    return StorefrontRuntimeSnapshot::fromArray($cached);
                } catch (Throwable $exception) {
                    report($exception);
                    Cache::forget($key);
                }
            }

            $connection = Config::string('queue.default');
            $driver = Config::get("queue.connections.{$connection}.driver");

            if (! in_array($driver, ['database', 'redis', 'sqs', 'beanstalkd'], true)) {
                return new StorefrontRuntimeSnapshot(
                    checkedAt: now()->toImmutable(),
                    error: 'Configure an asynchronous queue connection and run the storefronts worker to collect runtime information.',
                );
            }

            if (Cache::add($key.':requested', true, now()->addSeconds(self::TTL_SECONDS))) {
                try {
                    dispatch(new RecordStorefrontRuntimeSnapshotJob($deployment->id, $logs));
                } catch (Throwable $exception) {
                    Cache::forget($key.':requested');

                    throw $exception;
                }
            }

            return null;
        });
    }

    public function put(StorefrontDeployment $deployment, StorefrontRuntimeSnapshot $snapshot, bool $logs = false): void
    {
        $this->centrally(fn (): bool => Cache::put($this->key($deployment, $logs), $snapshot->toArray(), now()->addSeconds(self::TTL_SECONDS)));
    }

    private function key(StorefrontDeployment $deployment, bool $logs): string
    {
        $intent = hash('sha256', json_encode($deployment->intentFingerprint(), JSON_THROW_ON_ERROR));

        return 'vendra-store:runtime-snapshot:'.$deployment->id.':'.$intent.':'.($logs ? 'logs' : 'observation');
    }

    /**
     * Use the same cache namespace from tenant requests and the central worker.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    private function centrally(callable $callback): mixed
    {
        $tenant = Store::current();
        Store::forgetCurrent();

        try {
            return $callback();
        } finally {
            $tenant?->makeCurrent();
        }
    }
}
