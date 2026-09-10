<?php

declare(strict_types=1);

namespace Misaf\VendraAttributeApi\State;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\ApiResource\ResourceReference;
use Misaf\VendraApi\State\ResourceMapper;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraAttribute\Models\AttributeValue;
use Misaf\VendraAttributeApi\ApiResource\AttributeResource;
use UnexpectedValueException;

final class AttributeMapper implements ResourceMapper
{
    public function map(Model $model): AttributeResource
    {
        if (! $model instanceof Attribute) {
            throw new UnexpectedValueException('Expected an attribute model.');
        }

        return new AttributeResource(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            unit: $model->unit,
            position: $model->position,
            active: $model->active,
            values: $model->values
                ->map(fn (AttributeValue $value): ResourceReference => new ResourceReference($value->id, 'AttributeValue', $value->value))
                ->all(),
        );
    }
}
