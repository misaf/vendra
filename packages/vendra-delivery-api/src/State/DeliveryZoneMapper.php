<?php

declare(strict_types=1);

namespace Misaf\VendraDeliveryApi\State;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\State\ResourceMapper;
use Misaf\VendraDelivery\Models\DeliveryZone;
use Misaf\VendraDeliveryApi\ApiResource\DeliveryZoneResource;
use UnexpectedValueException;

final class DeliveryZoneMapper implements ResourceMapper
{
    public function map(Model $model): DeliveryZoneResource
    {
        if (! $model instanceof DeliveryZone) {
            throw new UnexpectedValueException('Expected a delivery zone model.');
        }

        return new DeliveryZoneResource(
            id: $model->id,
            name: self::translations($model, 'name'),
            description: self::translations($model, 'description') === []
                ? null
                : self::translations($model, 'description'),
            maxDistanceKm: $model->max_distance_km,
            currencyCode: $model->currency_code,
            feeAmount: (int) $model->fee_amount->getAmount(),
            requiresQuote: $model->requires_quote,
            position: $model->position,
        );
    }

    /**
     * @return array<string, string>
     */
    private static function translations(DeliveryZone $zone, string $attribute): array
    {
        $translations = [];

        foreach ($zone->getTranslations($attribute) as $locale => $value) {
            if (is_string($locale) && is_string($value)) {
                $translations[$locale] = $value;
            }
        }

        return $translations;
    }
}
