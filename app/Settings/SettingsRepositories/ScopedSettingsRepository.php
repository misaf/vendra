<?php

declare(strict_types=1);

namespace App\Settings\SettingsRepositories;

use App\Settings\SettingsScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Tenancy\TenantSchema;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository as SpatieDatabaseSettingsRepository;

/**
 * Queries key on the non-null scope column so upserts also work for platform
 * rows. Tenant global scopes are dropped, since they constrain nothing without
 * a current tenant.
 */
abstract class ScopedSettingsRepository extends SpatieDatabaseSettingsRepository
{
    /**
     * @return Builder<Model>
     */
    public function getBuilder(): Builder
    {
        return $this->builderForScope($this->scope());
    }

    /**
     * @param  mixed  $payload
     */
    public function createProperty(string $group, string $name, $payload, bool $locked = false): void
    {
        $this->persist([[
            'group' => $group,
            'name' => $name,
            'payload' => $this->encode($payload),
            'locked' => $locked,
        ]], ['payload', 'locked']);
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public function updatePropertiesPayload(string $group, array $properties): void
    {
        $rows = [];

        foreach ($properties as $name => $payload) {
            $rows[] = [
                'group' => $group,
                'name' => $name,
                'payload' => $this->encode($payload),
            ];
        }

        if ($rows === []) {
            return;
        }

        $this->persist($rows, ['payload']);
    }

    abstract protected function tenantId(): ?int;

    protected function scope(): string
    {
        return SettingsScope::forTenant($this->tenantId());
    }

    /**
     * @return Builder<Model>
     */
    protected function builderForScope(string $scope): Builder
    {
        return parent::getBuilder()
            ->withoutGlobalScopes()
            ->where('scope', $scope);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  list<string>  $update
     */
    private function persist(array $rows, array $update): void
    {
        $scope = $this->scope();
        $tenantId = $this->tenantId();
        $tenantColumn = TenantSchema::column();
        $hasTenantColumn = TenantSchema::hasTenantColumn(parent::getBuilder()->getModel()->getTable());

        $rows = array_map(
            static function (array $row) use ($scope, $tenantId, $tenantColumn, $hasTenantColumn): array {
                $row['scope'] = $scope;

                if ($hasTenantColumn) {
                    $row[$tenantColumn] = $tenantId;
                }

                return $row;
            },
            $rows,
        );

        /*
         | Written through the query builder rather than the model so the
         | tenant-stamping `creating` hook cannot put the current tenant on a
         | platform row. The scope this repository answers for is the only
         | thing that decides where a row lands.
         */
        $this->builderForScope($scope)->upsert($rows, ['scope', 'group', 'name'], $update);
    }
}
