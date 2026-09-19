<?php

declare(strict_types=1);

namespace Misaf\VendraDeliveryApi\State;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\State\Concerns\NormalizesResourceValues;
use Misaf\VendraApi\State\ResourceMapper;
use Misaf\VendraDelivery\Models\DeliveryZone;
use Misaf\VendraDeliveryApi\ApiResource\DeliveryZoneResource;
use UnexpectedValueException;

final class DeliveryZoneMapper implements ResourceMapper
{
    use NormalizesResourceValues;

    public function map(Model $model): DeliveryZoneResource
    {
        throw_unless($model instanceof DeliveryZone, UnexpectedValueException::class, 'Expected a delivery zone model.');

        $description = $this->normalizeTranslations($model->getTranslations('description'));

        return new DeliveryZoneResource(
            id: $model->id,
            name: $this->normalizeTranslations($model->getTranslations('name')),
            description: $description === [] ? null : $description,
            maxDistanceKm: $model->max_distance_km,
            currencyCode: $model->currency_code,
            feeAmount: (int) $model->fee_amount->getAmount(),
            requiresQuote: $model->requires_quote,
            position: $model->position,
        );
    }
}
