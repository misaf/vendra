<?php

declare(strict_types=1);

namespace App\Settings\SettingsRepositories;

use App\Settings\SettingsScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Tenancy\TenantSchema;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository as SpatieDatabaseSettingsRepository;

/**
 * A database settings repository that reads and writes one scope.
 *
 * Both concrete repositories share every query here; they differ only in the
 * tenant they answer for. Reads and writes are keyed on the non-null
 * {@see SettingsScope} column rather than on `tenant_id`, so an upsert conflicts
 * with the row it means to replace for platform rows too.
 *
 * The tenant global scopes on the property model are deliberately dropped: they
 * add no constraint at all when no tenant is current, which would let a console
 * request read whichever tenant's row happened to come first.
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

    /**
     * The tenant this repository answers for, or null for the platform.
     */
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
