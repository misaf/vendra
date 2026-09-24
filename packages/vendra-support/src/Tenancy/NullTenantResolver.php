<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Contracts\TenantResolver;

final class NullTenantResolver implements TenantResolver
{
    public function available(): bool
    {
        return false;
    }

    public function current(): ?Model
    {
        return null;
    }

    public function currentId(): ?int
    {
        return null;
    }

    public function modelClass(): string
    {
        return Model::class;
    }

    public function foreignKey(): string
    {
        return TenantSchema::DEFAULT_FOREIGN_KEY;
    }

    public function findByKeyOrSlug(int|string $tenant): ?Model
    {
        return null;
    }

    public function makeCurrent(Model|int|string $tenant): bool
    {
        return false;
    }

    /**
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function execute(Model|int|string $tenant, Closure $callback): mixed
    {
        return $callback();
    }

    public function eachTenant(Closure $callback): void
    {
        $callback();
    }

    /**
     * @return array<int, string>
     */
    public function searchOptions(string $value, int $limit = 10): array
    {
        return [];
    }
}
