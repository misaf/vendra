<?php

declare(strict_types=1);

namespace Misaf\VendraAddress\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraAddress\Models\Address;
use Misaf\VendraUserProfile\Models\UserProfile;

/** @extends Factory<Address> */
#[UseModel(Address::class)]
final class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_profile_id' => UserProfile::factory(),
            'type' => fake()->randomElement(['home', 'work', 'billing', 'shipping', 'other']),
            'label' => fake()->optional()->words(2, true),
            'recipient_name' => fake()->name(),
            'organization' => fake()->optional()->company(),
            'line_one' => fake()->streetAddress(),
            'line_two' => fake()->optional()->bothify('Unit ##??'),
            'line_three' => null,
            'locality' => fake()->city(),
            'administrative_area' => fake()->optional()->city(),
            'postal_code' => fake()->optional()->postcode(),
            'sorting_code' => null,
            'country_code' => fake()->countryCode(),
            'locale' => fake()->locale(),
            'metadata' => [],
            'notes' => fake()->optional()->sentence(),
            'is_primary' => false,
            'verified_at' => null,
        ];
    }

    public function forUserProfile(UserProfile $userProfile): static
    {
        return $this->state(fn (): array => [
            'user_profile_id' => $userProfile->id,
        ]);
    }
}
