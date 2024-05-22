<?php

declare(strict_types=1);

namespace App\Support\SwitchTenantTasks;

use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

final class SetConfigurationTask implements SwitchTenantTask
{
    public function forgetCurrent(): void {}

    public function makeCurrent(Tenant $tenant): void
    {
        $this->setSetting($tenant);
    }

    private function setSetting(Tenant $tenant): void
    {
        config([
            'app.asset_url'                => 'https://panel.lakipet.test',
            'app.locale'                   => 'fa',
            'app.name'                     => 'abc',
            'app.timezone'                 => 'Asia/Tehran',
            'app.url'                      => 'https://panel.lakipet.test',
            'cache.stores.file.path'       => storage_path('framework/cache/data/' . $tenant->slug),
            'filesystems.disks.public.url' => 'https://panel.lakipet.test',
            'session.cookie'               => '__Secure-' . $tenant->slug . '-session',
            'session.files'                => storage_path('framework/sessions/' . $tenant->slug),
        ]);
    }
}
