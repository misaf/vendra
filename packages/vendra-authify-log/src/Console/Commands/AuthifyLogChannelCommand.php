<?php

declare(strict_types=1);

namespace Misaf\VendraAuthifyLog\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Misaf\LaravelAuthifyLog\Jobs\AuthifyLogJob;
use Misaf\VendraSupport\Context\RequestJobContext;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraSupport\Tenancy\TenantSchema;

class AuthifyLogChannelCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'vendra-authify-log:channel';

    /**
     * @var string
     */
    protected $description = 'Processes messages from the authify-log-channel and dispatches jobs.';

    public function handle(): void
    {
        $redisConnection = Redis::connection('authify_log_channel');

        $redisConnection->subscribe(['authify-log-channel'], function (int $message): void {
            $cacheKey = 'entries';
            $batchSize = 100;

            echo "Received message: {$message}".PHP_EOL;

            if ($message < $batchSize) {
                return;
            }

            $entries = $this->getBatchEntries($cacheKey, $batchSize);

            if (empty($entries)) {
                echo 'No entries to process.'.PHP_EOL;

                return;
            }

            $this->processBatch($entries);
        });
    }

    /**
     * @return array<int, string>
     */
    private function getBatchEntries(string $cacheKey, int $batchSize): array
    {
        $connection = Redis::connection('authify_log');

        if (! $connection instanceof PhpRedisConnection) {
            throw new Exception('The authify log connection must use the PhpRedis driver.');
        }

        $result = $connection->transaction(function (\Redis $transaction) use ($cacheKey, $batchSize): void {
            $transaction->lrange($cacheKey, 0, $batchSize - 1);
            $transaction->ltrim($cacheKey, $batchSize, -1);
        });

        if (! is_array($result) || ! isset($result[0]) || ! is_array($result[0])) {
            return [];
        }

        return array_values(array_filter(
            $result[0],
            static fn (mixed $entry): bool => is_string($entry),
        ));
    }

    /**
     * @param  array<int, string>  $entries
     */
    private function processBatch(array $entries): void
    {
        /** @var array<int, array<int, array<string, int|string>>> $groupedEntries */
        $groupedEntries = [];

        foreach ($entries as $entry) {
            $decodedEntry = json_decode($entry, true);

            if (! is_array($decodedEntry)) {
                continue;
            }

            $tenantId = $decodedEntry[TenantSchema::column()] ?? 0;

            if (! is_int($tenantId) && ! is_string($tenantId)) {
                continue;
            }

            $normalizedEntry = [];

            foreach ($decodedEntry as $key => $value) {
                if (is_string($key) && (is_int($value) || is_string($value))) {
                    $normalizedEntry[$key] = $value;
                }
            }

            $groupedEntries[(int) $tenantId][] = $normalizedEntry;
        }

        foreach ($groupedEntries as $tenantId => $groupedLogs) {
            $this->dispatchJobForTenant($tenantId, $groupedLogs);
        }
    }

    /**
     * @param  array<int, array<string, int|string>>  $groupedLogs
     */
    private function dispatchJobForTenant(int $tenantId, array $groupedLogs): void
    {
        (new RequestJobContext(
            traceId: RequestJobContext::resolveTraceId(),
            operation: 'authify_log_batch',
            tenantId: $tenantId,
        ))->scope(function () use ($groupedLogs, $tenantId): void {
            try {
                $tenants = app(TenantResolver::class);

                if (TenantAwareness::enabled()) {
                    $tenant = $tenants->findByKeyOrSlug($tenantId);

                    if ($tenant === null) {
                        Log::error('Failed to dispatch job for tenant.', ["Tenant [{$tenantId}] was not found."]);

                        return;
                    }

                    $tenants->execute(
                        $tenant,
                        fn () => AuthifyLogJob::dispatch($groupedLogs),
                    );

                    return;
                }

                AuthifyLogJob::dispatch($groupedLogs);
            } catch (Exception $exception) {
                Log::error('Failed to dispatch job for tenant.', [$exception->getMessage()]);
            }
        });
    }
}
