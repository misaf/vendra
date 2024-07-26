<?php

declare(strict_types=1);

namespace App\Multitenancy\Tasks;

use Illuminate\Support\Facades\URL;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

final class SwitchAppTask implements SwitchTenantTask
{
    public function __construct(
        private ?array $original = null,
    ) {
        $this->original ??= config('app');
    }

    public function forgetCurrent(): void
    {
        $this->setLocale($this->original['locale']);
        $this->setName($this->original['name']);
        $this->setTimezone($this->original['timezone']);
        $this->setAppUrl($this->original['url']);
        $this->setAssetUrl($this->original['asset_url']);
    }

    public function makeCurrent(object $tenant): void
    {
        $this->setLocale('fa');
        $this->setName($tenant->name);
        $this->setTimezone('Asia/Tehran');
        $this->setAppUrl('https://panel.houshang-flowers.com');
        $this->setAssetUrl('https://panel.houshang-flowers.com');
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

    private function setAppUrl(string $url): void
    {
        // We may want to look into defining whether we want to use https at the tenant level
        // $scheme = parse_url($this->original)['scheme'];

        config([
            // 'app.url' => "{$scheme}://{domain}",
            'app.url' => $url,
        ]);

        URL::forceRootUrl(config('app.url'));
    }

    private function setAssetUrl(string $url): void
    {
        // We may want to look into defining whether we want to use https at the tenant level
        // $scheme = parse_url($this->original)['scheme'];

        config([
            // 'app.url' => "{$scheme}://{domain}",
            'app.asset_url' => $url,
        ]);
    }
}
