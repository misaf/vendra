<?php

declare(strict_types=1);

namespace App\Multitenancy\Tasks;

use Illuminate\Support\Facades\URL;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;
use Termehsoft\Tenant\Models\Tenant;

final class SwitchAppTask implements SwitchTenantTask
{
    public function __construct(
        private ?string $original = null,
    ) {
        $this->original ??= config('app');
    }

    public function forgetCurrent(): void
    {
        $this->setLocale($this->original['locale']);
        $this->setName($this->original['name']);
        $this->setTimezone($this->original['timezone']);
        $this->setUrl($this->original['url']);
    }

    public function makeCurrent(Tenant $tenant): void
    {
        $this->setLocale($tenant->locale);
        $this->setName($tenant->name);
        $this->setTimezone($tenant->timezone);
        $this->setUrl($tenant->url);
    }

    private function setLocale(string $locale): void
    {
        config([
            'app.locale' => $locale,
        ]);
    }

    private function setName(string $name): void
    {
        config([
            'app.name' => $name,
        ]);
    }

    private function setTimezone(string $timezone): void
    {
        config([
            'app.timezone' => $timezone,
        ]);
    }

    private function setUrl(string $url): void
    {
        // We may want to look into defining whether we want to use https at the tenant level
        $scheme = parse_url($this->original)['scheme'];

        config([
            'app.url' => "{$scheme}://{domain}",
        ]);

        URL::forceRootUrl(config('app.url'));
    }
}
