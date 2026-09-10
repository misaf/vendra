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
        throw_unless($model instanceof AttributeValue, UnexpectedValueException::class, 'Expected an attribute value model.');

        $attribute = $model->attribute;

        throw_unless($attribute instanceof Attribute, UnexpectedValueException::class, 'An attribute value must belong to an attribute.');

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
