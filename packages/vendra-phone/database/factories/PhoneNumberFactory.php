<?php

declare(strict_types=1);

namespace Misaf\VendraPhone\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraPhone\Models\PhoneNumber;
use Misaf\VendraUserProfile\Models\UserProfile;

/** @extends Factory<PhoneNumber> */
#[UseModel(PhoneNumber::class)]
final class PhoneNumberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_profile_id' => UserProfile::factory(),
            'type' => fake()->randomElement(['mobile', 'home', 'work', 'fax', 'other']),
            'label' => fake()->optional()->words(2, true),
            'country_code' => 'US',
            'number' => '+1'.fake()->numerify('##########'),
            'extension' => fake()->optional()->numerify('####'),
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
