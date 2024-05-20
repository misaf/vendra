<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contract\Language;
use App\Models\Language\Language as LanguageLanguage;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(Language::class, LanguageLanguage::class);

        Model::shouldBeStrict( ! $this->app->environment('production'));

        URL::forceScheme('https');

        $this->app['request']->server->set('HTTPS', 'on');

        if ($this->app->environment('production')) {
            Password::defaults(fn() => Password::min(8)->mixedCase());
        }

        config()->set([
            'app.name'      => 'asd',
            'session.cookie' => str()->slug('asd', '_') . '_session',
            'app.url'       => 'https://panel.houshang-flowers.test',
            'app.asset_url' => 'https://panel.houshang-flowers.test',
        ]);

        // app()->singleton('url', function ($app) {
        //     return new UrlGenerator(
        //         $app['router']->getRoutes(),
        //         $app->rebinding(
        //             'request',
        //             function ($app2, $request): void {
        //                 $app2['url']->setRequest($request);
        //             },
        //         ),
        //         null,
        //     );
        // });

        // Lang::handleMissingKeysUsing(function (string $key, array $replacements, string $locale) {
        //     info("Missing translation key [{$key}] detected.");

        //     return $key;
        // });

        // DB::listen(fn($query) => Log::info($query->sql, $query->bindings));
    }
}
