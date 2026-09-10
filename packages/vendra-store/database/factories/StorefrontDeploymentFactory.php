<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Models\StorefrontImage;

/**
 * @extends Factory<StorefrontDeployment>
 */
final class StorefrontDeploymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'storefront_image_id' => StorefrontImage::factory(),
            'slug' => fake()->unique()->slug(2),
            'domain' => fake()->unique()->domainName(),
            'configuration' => [
                'name' => ['en' => fake()->company(), 'fa' => 'گل‌فروشی'],
            ],
            'status' => StorefrontDeploymentStatus::Pending,
            'desired_state' => StorefrontDesiredState::Running,
        ];
    }
}
