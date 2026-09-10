<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraAttribute\Models\AttributeValue;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/** @extends Factory<AttributeValue> */
#[UseModel(AttributeValue::class)]
final class AttributeValueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::factory(),
            'value' => fake()->word(),
        ];
    }

    public function forAttribute(Attribute $attribute): static
    {
        return $this->state(fn (): array => ['attribute_id' => $attribute->id]);
    }

    public function forAttributable(Model $attributable): static
    {
        return $this->state(fn (): array => [
            'attributable_type' => $attributable->getMorphClass(),
            'attributable_id' => $attributable->getKey(),
        ]);
    }

    public function forTenant(Model|int $tenant): static
    {
        if (! TenantAwareness::enabled()) {
            return $this;
        }

        return $this->state(fn (): array => [
            'tenant_id' => $tenant instanceof Model ? $tenant->getKey() : $tenant,
        ]);
    }
}
