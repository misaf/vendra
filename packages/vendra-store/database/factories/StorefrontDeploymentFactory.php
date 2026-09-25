<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Models\StorefrontImage;

/**
 * @extends Factory<StorefrontDeployment>
 */
#[UseModel(StorefrontDeployment::class)]
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
                'businessType' => 'Florist',
                'priceCurrency' => 'IRR',
                'address' => ['locality' => 'Tehran', 'country' => 'IR'],
                'contact' => [
                    'mobilePhone' => '09120000000',
                    'officePhone' => '02100000000',
                    'email' => 'contact@example.test',
                    'hoursOpen' => '08:00',
                    'hoursClose' => '21:00',
                    'mapQuery' => '35.7,51.4',
                ],
                'social' => [
                    'whatsappPhone' => '+989120000000',
                    'telegramUsername' => 'flowers',
                    'instagramUsername' => 'flowers',
                ],
            ],
            'status' => StorefrontDeploymentStatus::Pending,
            'desired_state' => StorefrontDesiredState::Running,
        ];
    }
}
