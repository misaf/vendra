<?php

declare(strict_types=1);

namespace Misaf\VendraAttributeApi\State;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\ApiResource\ResourceReference;
use Misaf\VendraApi\State\ResourceMapper;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraAttribute\Models\AttributeValue;
use Misaf\VendraAttributeApi\ApiResource\AttributeValueResource;
use UnexpectedValueException;

final class AttributeValueMapper implements ResourceMapper
{
    public function map(Model $model): AttributeValueResource
    {
        if (! $model instanceof AttributeValue) {
            throw new UnexpectedValueException('Expected an attribute value model.');
        }

        $attribute = $model->attribute;

        if (! $attribute instanceof Attribute) {
            throw new UnexpectedValueException('An attribute value must belong to an attribute.');
        }

        return new AttributeValueResource(
            id: $model->id,
            value: $model->value,
            position: $model->position,
            attribute: new ResourceReference(
                $attribute->id,
                'Attribute',
                $attribute->name,
            ),
        );
    }
}
