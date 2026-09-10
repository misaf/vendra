<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraCurrency\Enums\CurrencyType;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraCurrency\Support\CurrencyRegistry;

/**
 * @extends Factory<Currency>
 */
#[UseModel(Currency::class)]
final class CurrencyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = fake()->unique()->randomElement(
            array_keys(CurrencyRegistry::options(CurrencyType::Fiat)),
        );

        return [
            'code' => $code,
            'name' => CurrencyRegistry::nameFor($code),
            'symbol' => null,
            'decimal_places' => CurrencyRegistry::minorUnitFor($code) ?? 2,
            'type' => CurrencyType::Fiat,
            'active' => true,
            'is_default' => false,
            'position' => fake()->numberBetween(1, 1000),
        ];
    }

    public function default(): static
    {
        return $this->state(fn (): array => ['is_default' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['active' => false]);
    }

    public function crypto(): static
    {
        return $this->state(function (): array {
            $code = fake()->unique()->randomElement(
                array_keys(CurrencyRegistry::options(CurrencyType::Crypto)),
            );

            return [
                'code' => $code,
                'name' => CurrencyRegistry::nameFor($code),
                'decimal_places' => CurrencyRegistry::minorUnitFor($code) ?? 8,
                'type' => CurrencyType::Crypto,
            ];
        });
    }

    public function code(string $code): static
    {
        return $this->state(fn (): array => [
            'code' => $code,
            'name' => CurrencyRegistry::nameFor($code),
            'decimal_places' => CurrencyRegistry::minorUnitFor($code) ?? 2,
            'type' => CurrencyRegistry::typeFor($code) ?? CurrencyType::Fiat,
        ]);
    }
}
