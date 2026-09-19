<?php

declare(strict_types=1);

namespace Misaf\VendraDeliveryApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Misaf\VendraApi\State\Concerns\NormalizesResourceValues;
use Misaf\VendraDelivery\Models\DeliveryZone;
use Misaf\VendraDelivery\Support\DeliveryZoneMatcher;
use Misaf\VendraDeliveryApi\ApiResource\DeliveryQuoteResource;
use Misaf\VendraDeliveryApi\ApiResource\QuotedDelivery;

/**
 * Price a dropped pin without writing anything, so it can run on every drag.
 *
 * @implements ProcessorInterface<DeliveryQuoteResource, QuotedDelivery>
 */
final readonly class QuoteDeliveryProcessor implements ProcessorInterface
{
    use NormalizesResourceValues;

    public function __construct(private DeliveryZoneMatcher $zoneMatcher) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): QuotedDelivery
    {
        $quote = $this->zoneMatcher->quoteFor(
            latitude: $data->latitude,
            longitude: $data->longitude,
            currencyCode: $data->currencyCode === null ? null : mb_strtoupper($data->currencyCode),
        );

        return new QuotedDelivery(
            id: 'current',
            zoneId: $quote->zone?->id,
            zoneName: $quote->zone instanceof DeliveryZone ? $this->normalizeTranslations($quote->zone->getTranslations('name')) : null,
            distanceKm: $quote->distanceKm,
            feeAmount: $quote->feeAmount,
            currencyCode: $quote->currencyCode,
            requiresQuote: $quote->requiresQuote,
        );
    }
}
