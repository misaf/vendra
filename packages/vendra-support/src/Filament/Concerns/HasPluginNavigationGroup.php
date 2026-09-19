<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Concerns;

use Closure;
use Illuminate\Support\Facades\Config;

/**
 * A fluent override wins, then `<plugin-id>.navigation_group` config, then the
 * default. The key is translated at call time so the request locale applies.
 */
trait HasPluginNavigationGroup
{
    protected string|Closure|null $navigationGroup = null;

    abstract public function getId(): string;

    /**
     * Get the default `vendra-support::navigation.groups.*` key.
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
