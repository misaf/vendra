<?php

declare(strict_types=1);

namespace App\Multitenancy\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Concerns\UsesTenantModel;
use Spatie\Multitenancy\TenantFinder\TenantFinder;
use Termehsoft\Tenant\Models\Tenant;

final class DomainTenantFinder extends TenantFinder
{
    use UsesTenantModel;

    public function findForRequest(Request $request): ?Tenant
    {
        $host = $request->getHost();

        return $this->getTenantModel()::whereHas('tenantDomains', fn(Builder $builder) => $builder->where('name', $host)->where('status', true))->where('status', true)->first();
    }
}
