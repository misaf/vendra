<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Pages;

use Filament\Clusters\Cluster;
use Filament\Pages\SettingsPage;
use Misaf\VendraSupport\Filament\Clusters\SystemCluster;

/**
 * Base for a package's store settings page in the admin System cluster.
 *
 * Each package edits only its own settings class here, read from and saved to
 * the current store's scope, with the platform row as the default.
 */
abstract class SystemSettingsPage extends SettingsPage
{
    /**
     * @var class-string<Cluster>|null
     */
    protected static ?string $cluster = SystemCluster::class;

    protected ?bool $hasDatabaseTransactions = true;

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }
}
