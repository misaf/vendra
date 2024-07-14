<?php

declare(strict_types=1);

namespace App\Models\Tenant\Services;

use App\Models\Tenant\Tenant;

final class SetGlobalConfigurationTask
{
    public static function makeConfiguration(Tenant $tenant): void
    {
        config()->set([
            'app.asset_url'                => 'https://panel.houshang-flowers.test',
            'app.locale'                   => 'fa',
            'app.name'                     => 'abc',
            'app.timezone'                 => 'Asia/Tehran',
            'app.url'                      => 'https://panel.houshang-flowers.test',
            // 'cache.stores.file.path'       => storage_path('framework/cache/data/' . $tenant->name),
            'filesystems.disks.public.url' => 'https://panel.houshang-flowers.test',
            'session.cookie'               => '__Secure-' . $tenant->domain . '-session',
            // 'session.files'                => storage_path('framework/sessions/' . $tenant->name),
        ]);
    }
}
