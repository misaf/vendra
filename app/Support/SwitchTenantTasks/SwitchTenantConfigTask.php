<?php

declare(strict_types=1);

namespace App\Support\SwitchTenantTasks;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\URL;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

final class SwitchTenantConfigTask implements SwitchTenantTask
{
    public function forgetCurrent(): void {}

    public function makeCurrent(Tenant $tenant): void
    {
        config()->set([
            'app.name'      => 'xxx',
            'app.url'       => 'https://panel.houshang-flowers.test',
            'app.asset_url' => 'https://panel.houshang-flowers.test',
        ]);

        // app()->singleton('url', function ($app) {
        //     return new UrlGenerator(
        //         $app['router']->getRoutes(),
        //         $app->rebinding(
        //             'request',
        //             function ($app2, $request) {
        //                 $app2['url']->setRequest($request);
        //             }
        //         ),
        //         null
        //     );
        // });
    }
}
