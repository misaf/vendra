<?php

declare(strict_types=1);

namespace Misaf\VendraDeliveryApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Misaf\VendraDelivery\Models\DeliveryZone;
use Misaf\VendraDelivery\Support\DeliveryZoneMatcher;
use Misaf\VendraDeliveryApi\ApiResource\DeliveryQuoteResource;
use Misaf\VendraDeliveryApi\ApiResource\QuotedDelivery;

/**
 * Price one dropped pin.
 *
 * The answer is deliberately read-only: nothing is reserved and nothing is
 * written, so the storefront may call it on every drag of the map pin.
 *
 * @implements ProcessorInterface<DeliveryQuoteResource, QuotedDelivery>
 */
final readonly class QuoteDeliveryProcessor implements ProcessorInterface
{
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
            zoneName: $quote->zone instanceof DeliveryZone ? self::translations($quote->zone) : null,
            distanceKm: $quote->distanceKm,
            feeAmount: $quote->feeAmount,
            currencyCode: $quote->currencyCode,
            requiresQuote: $quote->requiresQuote,
        );
    }

    /**
     * @return array<string, string>
     */
    private static function translations(DeliveryZone $zone): array
    {
        $translations = [];

        foreach ($zone->getTranslations('name') as $locale => $value) {
            if (is_string($locale) && is_string($value)) {
                $translations[$locale] = $value;
            }
        }

        return $translations;
    }
}
