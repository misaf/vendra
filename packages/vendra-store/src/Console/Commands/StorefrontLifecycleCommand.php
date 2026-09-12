<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Misaf\VendraStore\Actions\RestartStoreStorefrontAction;
use Misaf\VendraStore\Actions\StartStoreStorefrontAction;
use Misaf\VendraStore\Actions\StopStoreStorefrontAction;
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontReference;

/**
 * Administrator access to a single storefront's lifecycle.
 *
 * Deliberately separate from `storefront:reconcile`: these commands act on one
 * storefront, change no configuration, and record intent — stopping a storefront
 * here means it stays stopped through the next reconciliation pass.
 *
 * start/stop/restart record intent, so each is an action. status and logs
 * record nothing and decide nothing — they are reads, and go straight to the
 * provisioner port rather than through a pass-through wrapper.
 */
#[Description('Start, stop, restart, or inspect one store storefront')]
#[Signature('storefront:lifecycle
        {action : start, stop, restart, status, or logs}
        {slug : The storefront slug}
        {--lines=200 : Log lines to show for the logs action}')]
final class StorefrontLifecycleCommand extends Command
{
    public function handle(
        StartStoreStorefrontAction $start,
        StopStoreStorefrontAction $stop,
        RestartStoreStorefrontAction $restart,
        StorefrontProvisioner $provisioner,
    ): int {
        $slug = (string) $this->argument('slug');
        $deployment = StorefrontDeployment::query()->where('slug', $slug)->first();

        if (! $deployment instanceof StorefrontDeployment) {
            $this->error("No storefront deployment named [{$slug}] exists.");

            return self::FAILURE;
        }

        return match ((string) $this->argument('action')) {
            'start' => $this->perform(fn () => $start->execute($deployment), "Storefront [{$slug}] started."),
            'stop' => $this->perform(fn () => $stop->execute($deployment), "Storefront [{$slug}] stopped."),
            'restart' => $this->perform(fn () => $restart->execute($deployment), "Storefront [{$slug}] restarted."),
            'status' => $this->reportStatus($provisioner, $deployment),
            'logs' => $this->reportLogs($provisioner, $deployment),
            default => $this->unknownAction(),
        };
    }

    /**
     * @param  callable(): void  $operation
     */
    private function perform(callable $operation, string $message): int
    {
        $operation();

        $this->info($message);

        return self::SUCCESS;
    }

    private function reportStatus(StorefrontProvisioner $provisioner, StorefrontDeployment $deployment): int
    {
        $observed = $provisioner->observe(StorefrontReference::for($deployment));

        $this->table(['Field', 'Value'], [
            ['Slug', $deployment->slug],
            ['Domain', $deployment->domain],
            ['Recorded status', $deployment->status->value],
            ['Desired state', $deployment->desired_state->value],
            ['Runtime state', $observed->state->value],
            ['Container', $observed->containerName ?? $deployment->container_name ?? '—'],
            ['Running image', $observed->image ?? '—'],
            ['Image digest', $deployment->image_digest ?? '—'],
        ]);

        return self::SUCCESS;
    }

    private function reportLogs(StorefrontProvisioner $provisioner, StorefrontDeployment $deployment): int
    {
        $lines = (int) $this->option('lines');

        $this->line($provisioner->logs(StorefrontReference::for($deployment), $lines > 0 ? $lines : 200));

        return self::SUCCESS;
    }

    private function unknownAction(): int
    {
        $this->error('Unknown action. Use start, stop, restart, status, or logs.');

        return self::FAILURE;
    }
}
