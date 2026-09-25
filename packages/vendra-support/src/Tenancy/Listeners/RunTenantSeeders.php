<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\Events\TenantProvisioned;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use UnexpectedValueException;

/**
 * Runs every registered module's seeders inside the new tenant.
 *
 * The seeders run directly rather than through their Artisan seed commands, so
 * provisioning also works inside a web request (a sync queue), where the
 * console-only seed commands are not registered. The previous tenant is
 * restored afterwards.
 */
final readonly class RunTenantSeeders
{
    public function __construct(
        private TenantSeeders $seeders,
        private TenantResolver $tenants,
    ) {}

    public function handle(TenantProvisioned $event): void
    {
        if (! $event->shouldSeed) {
            return;
        }

        $this->tenants->execute($event->tenant, function (): void {
            foreach ($this->seeders->ordered() as $command) {
                foreach ($command::seeders() as $seederClass) {
                    $this->run($seederClass);
                }
            }
        });
    }

    /**
     * Run a seeder the way `db:seed` does: resolved from the container, with mass-assignment guards off.
     *
     * @param  class-string<Seeder>  $seederClass
     */
    private function run(string $seederClass): void
    {
        $seeder = resolve($seederClass);

        throw_unless($seeder instanceof Seeder, UnexpectedValueException::class, sprintf('[%s] is not a seeder.', $seederClass));

        Model::unguarded(fn (): mixed => $seeder->setContainer(app())->__invoke());
    }
}
