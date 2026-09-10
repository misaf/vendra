<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Actions\Roles\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Misaf\VendraPermission\Models\Role;

trait ResolvesSelected
{
    /**
     * @param  array{roles?: mixed}  $data
     * @return array<string, list<ModelKey>>
     */
    private function resolveRoleIdsByGuardFromPayload(array $data): array
    {
        $rawRoleIds = Arr::get($data, 'roles', null);

        throw_unless(is_array($rawRoleIds), InvalidArgumentException::class, 'Invalid roles provided.');

        /** @var list<ModelKey> $roleIds */
        $roleIds = [];

        foreach ($rawRoleIds as $rawRoleId) {
            if (is_int($rawRoleId) || is_string($rawRoleId)) {
                $roleIds[] = $rawRoleId;
            }
        }

        $roleIds = array_values(array_unique($roleIds, SORT_REGULAR));

        throw_if($roleIds === [], InvalidArgumentException::class, 'Invalid roles provided.');

        /** @var Collection<int, Role> $roles */
        $roles = Role::query()
            ->whereKey($roleIds)
            ->get(['id', 'guard_name']);

        throw_if($roles->count() !== count($roleIds), InvalidArgumentException::class, 'Invalid roles provided.');

        /** @var array<string, list<ModelKey>> $resolvedRoleIdsByGuard */
        $resolvedRoleIdsByGuard = $roles
            ->groupBy('guard_name')
            ->map(
                /**
                 * @param  Collection<int, Role>  $rolesInGuard
                 * @return list<ModelKey>
                 */
                static fn(Collection $rolesInGuard): array => $rolesInGuard
                    ->map(static fn (Role $role): int => $role->id)
                    ->values()
                    ->all()
            )
            ->all();

        return $resolvedRoleIdsByGuard;
    }
}
