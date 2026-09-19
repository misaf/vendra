<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Enums\StorefrontRuntimeState;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontReference;
use Throwable;

#[Description('List storefront deployments from the database')]
#[Signature('vendra-store:status {--runtime : Also ask the container runtime what it actually has}')]
final class StorefrontDeploymentStatusCommand extends Command
{
    public function handle(StorefrontProvisioner $provisioner): int
    {
        $deployments = StorefrontDeployment::query()
            ->select(['slug', 'domain', 'status', 'desired_state', 'container_name', 'image_digest'])
            ->orderBy('slug')
            ->get();

        if ($deployments->isEmpty()) {
            $this->info('No storefront deployments found.');

            return self::SUCCESS;
        }

        if (! $this->option('runtime')) {
            $this->table(
                ['Slug', 'Domain', 'Status', 'Desired', 'Container', 'Image digest'],
                $deployments->map(fn (StorefrontDeployment $deployment): array => [
                    ...$this->recordedColumns($deployment),
                    $deployment->image_digest ?? '—',
                ])->all(),
            );

            return self::SUCCESS;
        }

        $observed = $deployments->mapWithKeys(
            fn (StorefrontDeployment $deployment): array => [$deployment->slug => $this->observe($provisioner, $deployment)],
        );

        $this->table(
            ['Slug', 'Domain', 'Status', 'Desired', 'Container', 'Runtime'],
            $deployments->map(fn (StorefrontDeployment $deployment): array => [
                ...$this->recordedColumns($deployment),
                $this->describeRuntime($deployment, $observed->get($deployment->slug)),
            ])->all(),
        );

        return $this->reportMissing($deployments, $observed);
    }

    /**
     * @return list<string>
     */
    private function recordedColumns(StorefrontDeployment $deployment): array
    {
        return [
            $deployment->slug,
            $deployment->domain,
            $deployment->status->value,
            $deployment->desired_state->value,
            $deployment->container_name ?? '—',
        ];
    }

    /**
     * Report an unreachable runtime per row so the recorded state still prints.
     */
    private function observe(StorefrontProvisioner $provisioner, StorefrontDeployment $deployment): ?StorefrontRuntimeState
    {
        try {
            return $provisioner->observe(StorefrontReference::for($deployment))->state;
        } catch (Throwable) {
            return null;
        }
    }

    private function describeRuntime(StorefrontDeployment $deployment, ?StorefrontRuntimeState $state): string
    {
        if ($state === null) {
            return '<fg=yellow>unreachable</>';
        }

        if ($state !== StorefrontRuntimeState::Absent) {
            return $state->value;
        }

        return $deployment->desired_state->expectsRunning()
            ? '<fg=red>absent</>'
            : 'absent';
    }

    /**
     * Warn when the runtime has no container for a deployed storefront.
     *
     * The usual cause is a changed `CONTAINER_DRIVER` or host, which leaves the
     * old containers running unmanaged on the previous daemon.
     *
     * @param  Collection<int, StorefrontDeployment>  $deployments
     * @param  Collection<string, StorefrontRuntimeState|null>  $observed
     */
    private function reportMissing(Collection $deployments, Collection $observed): int
    {
        $missing = $deployments
            ->filter(fn (StorefrontDeployment $deployment): bool => $deployment->desired_state->expectsRunning()
                && $observed->get($deployment->slug) === StorefrontRuntimeState::Absent)
            ->pluck('slug');

        if ($missing->isEmpty()) {
            return self::SUCCESS;
        }

        $this->newLine();
        $this->components->error(sprintf(
            'The runtime has no container for: %s. If the endpoint or runtime was changed, those containers are '
            .'still on the previous daemon — run container:status to confirm which one is answering, then '
            .'vendra-store:redeploy to place them here.',
            $missing->implode(', '),
        ));

        return self::FAILURE;
    }
}
