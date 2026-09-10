<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliateApi\State;

use Illuminate\Support\Arr;
use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraAffiliate\Enums\AffiliateStatusEnum;
use Misaf\VendraAffiliate\Models\Affiliate;

/**
 * @implements LinksHandlerInterface<Affiliate>
 */
final class AffiliateLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<Affiliate>  $builder
     * @return Builder<Affiliate>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $builder
            ->select(['id', 'user_id', 'code', 'commission_percent', 'signup_bounty', 'status', 'created_at'])
            ->where('status', AffiliateStatusEnum::Active);

        if (! (Arr::get($context, 'operation', null)) instanceof CollectionOperationInterface) {
            $mcpData = Arr::get($context, 'mcp_data', []);
            $builder->whereKey(Arr::get($uriVariables, 'id', is_array($mcpData) ? (Arr::get($mcpData, 'id', null)) : null));
        }

        return $builder;
    }
}
