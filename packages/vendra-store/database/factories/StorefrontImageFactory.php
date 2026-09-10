<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraStore\Models\StorefrontImage;

/**
 * @extends Factory<StorefrontImage>
 */
final class StorefrontImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<StorefrontImage>
     */
    protected $model = StorefrontImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image' => 'ghcr.io/misaf/vendra-storefront-'.fake()->unique()->slug().'@sha256:'.fake()->sha256(),
            'active' => true,
        ];
    }
}
