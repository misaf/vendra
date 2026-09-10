<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\VendraSupport\Capabilities\EloquentTagResolver;
use Misaf\VendraSupport\Contracts\TagResolver;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Support\TagRelationship;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Misaf\VendraTagger\Console\Commands\SeedCommand;
use Misaf\VendraTagger\Models\Tagger;
use Misaf\VendraTagger\TaggerPlugin;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class TaggerServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-tagger')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                'create_tag_tables',
            ])
            ->hasCommands(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-tagger');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(
            TagResolver::class,
            function (): EloquentTagResolver {
                $morphName = Config::string('tags.taggable.morph_name', 'taggable');
                $pivotModel = Config::string('tags.taggable.class_name', MorphPivot::class);

                if (! is_a($pivotModel, MorphPivot::class, true)) {
                    $pivotModel = MorphPivot::class;
                }

                return new EloquentTagResolver(new TagRelationship(
                    model: Tagger::class,
                    morphName: $morphName,
                    table: Config::string('tags.taggable.table_name', 'taggables'),
                    foreignPivotKey: "{$morphName}_id",
                    pivotModel: $pivotModel,
                ));
            },
        );

        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-tagger')) {
                return;
            }

            $panel->plugin(TaggerPlugin::make());
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register('tags');
        $this->app->make(TenantSeeders::class)->register('vendra-tagger:seed', priority: 70);

        AboutCommand::add('Vendra Tagger', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-tagger')]);
    }
}
