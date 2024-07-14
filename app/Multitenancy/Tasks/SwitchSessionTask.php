<?php

declare(strict_types=1);

namespace App\Multitenancy\Tasks;

use Spatie\Multitenancy\Tasks\SwitchTenantTask;
use Termehsoft\Tenant\Models\Tenant;

final class SwitchSessionTask implements SwitchTenantTask
{
    public function __construct(
        protected ?string $originalSessionDriver = null,
        protected ?string $originalSessionConnection = null,
        protected ?string $originalSessionDomain = null,
    ) {
        $this->originalSessionDriver ??= config('session.driver');
        $this->originalSessionConnection ??= config('session.connection');
        $this->originalSessionDomain ??= config('session.domain');
    }

    public function forgetCurrent(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $this->setSessionConfig(
            driver: $this->originalSessionDriver,
            connection: $this->originalSessionConnection,
            domain: $this->originalSessionDomain,
        );
    }

    public function makeCurrent(Tenant $tenant): void
    {
        // Not going to switch the session driver in testing, stick with the default array driver
        if (app()->runningUnitTests()) {
            return;
        }

        $this->setSessionConfig(
            driver: config('session.driver'),
            connection: config('session.connection'),
            domain: $tenant->domain,
        );
    }

    private function setSessionConfig(string $driver, string $connection, string $domain): void
    {
        config([
            'session.driver'     => $driver,
            'session.connection' => $connection,
            'session.domain'     => $domain,
        ]);

        app()->forgetInstance('session');
        app()->forgetInstance('session.store');
    }
}
