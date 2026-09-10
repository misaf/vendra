<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliateApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\Request;
use Misaf\VendraAffiliate\Actions\RecordAffiliateClickAction;
use Misaf\VendraAffiliate\Enums\AffiliateStatusEnum;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliateApi\ApiResource\AffiliateClickResource;

/**
 * @implements ProcessorInterface<AffiliateClickResource, void>
 */
final readonly class RecordReferralVisitProcessor implements ProcessorInterface
{
    public function __construct(
        private RecordAffiliateClickAction $recordAffiliateClick,
        private Request $request,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $affiliate = Affiliate::query()
            ->where('code', $data->code)
            ->where('status', AffiliateStatusEnum::Active)
            ->first();

        if (! $affiliate instanceof Affiliate) {
            return;
        }

        $this->recordAffiliateClick->execute(
            affiliate: $affiliate,
            ipAddress: $this->request->ip(),
            userAgent: $this->request->userAgent(),
            referer: $this->request->headers->get('referer'),
            landingUrl: $data->landingUrl,
        );
    }
}
