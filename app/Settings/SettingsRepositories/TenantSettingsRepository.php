<?php

declare(strict_types=1);

namespace App\Settings\SettingsRepositories;

use App\Settings\SettingsScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Contracts\TenantResolver;

/**
 * Store-scoped settings, with the platform row as the default.
 *
 * A tenant only ever owns the properties it has actually saved. Everything else
 * is read from the platform row the settings migration created, which is what
 * keeps a freshly provisioned store from throwing `MissingSettings` on its
 * first visit to a settings page and removes any need to seed a row per tenant.
 *
 * Reads therefore union exactly two scopes — the platform's and the current
 * tenant's — and never see another tenant's rows. Writes always land in the
 * current tenant's scope, so saving a store's settings can never overwrite the
 * platform default other stores still read.
 */
final class TenantSettingsRepository extends ScopedSettingsRepository
{
    /**
     * @return array<string, mixed>
     */
    public function getPropertiesInGroup(string $group): array
    {
        $properties = $this->propertiesInScope($this->platformBuilder(), $group);

        if ($this->onPlatformScope()) {
            return $properties;
        }

        return array_merge($properties, $this->propertiesInScope($this->getBuilder(), $group));
    }

    public function checkIfPropertyExists(string $group, string $name): bool
    {
        if (parent::checkIfPropertyExists($group, $name)) {
            return true;
        }

        return ! $this->onPlatformScope() && $this->platformBuilder()
            ->where('group', $group)
            ->where('name', $name)
            ->exists();
    }

    public function getPropertyPayload(string $group, string $name): mixed
    {
        $payload = $this->getBuilder()
            ->where('group', $group)
            ->where('name', $name)
            ->value('payload');

        if ($payload === null && ! $this->onPlatformScope()) {
            $payload = $this->platformBuilder()
                ->where('group', $group)
                ->where('name', $name)
                ->value('payload');
        }

        return is_string($payload) ? $this->decode($payload) : null;
    }

    protected function tenantId(): ?int
    {
        if (! app()->bound(TenantResolver::class)) {
            return null;
        }

        return resolve(TenantResolver::class)->currentId();
    }

    /**
     * @return Builder<Model>
     */
    private function platformBuilder(): Builder
    {
        return $this->builderForScope(SettingsScope::PLATFORM);
    }

    private function onPlatformScope(): bool
    {
        return SettingsScope::isPlatform($this->scope());
    }

    /**
     * @param  Builder<Model>  $builder
     * @return array<string, mixed>
     */
    private function propertiesInScope(Builder $builder, string $group): array
    {
        $properties = [];

        foreach ($builder->where('group', $group)->get(['name', 'payload']) as $property) {
            $name = $property->getAttribute('name');
            $payload = $property->getAttribute('payload');

            if (is_string($name) && is_string($payload)) {
                $properties[$name] = $this->decode($payload, true);
            }
        }

        return $properties;
    }
}
