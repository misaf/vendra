<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Concerns;

use Closure;
use Illuminate\Support\Facades\Config;

/**
 * Standard navigation-group resolution for Vendra panel plugins: an explicit
 * fluent override wins, then the module's `<plugin-id>.navigation_group`
 * config key, then the module's default `vendra-support::navigation.groups.*`
 * key. The resolved key is translated at call time so the request locale is
 * honoured after locale middleware runs.
 */
trait HasPluginNavigationGroup
{
    protected string|Closure|null $navigationGroup = null;

    abstract public function getId(): string;

    /**
     * The `vendra-support::navigation.groups.*` key used when neither a
     * fluent override nor module config provides a group.
     */
    abstract protected function defaultNavigationGroup(): string;

    public function navigationGroup(string|Closure|null $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): string
    {
        $group = $this->navigationGroup ?? Config::get($this->getId().'.navigation_group');

        if ($group instanceof Closure) {
            $group = $group();
        }

        if (! is_string($group) || $group === '') {
            $group = $this->defaultNavigationGroup();
        }

        return (string) __($group);
    }
}
