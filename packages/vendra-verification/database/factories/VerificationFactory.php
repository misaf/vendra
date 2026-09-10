<?php

declare(strict_types=1);

namespace Misaf\VendraVerification\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraUserProfile\Models\UserProfile;
use Misaf\VendraVerification\Models\Verification;

/** @extends Factory<Verification> */
#[UseModel(Verification::class)]
final class VerificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_profile_id' => UserProfile::factory(),
            'type' => fake()->randomElement(['identity', 'address', 'phone', 'document', 'kyc', 'other']),
            'status' => 'pending',
            'provider' => fake()->optional()->company(),
            'reference' => fake()->optional()->uuid(),
            'country_code' => fake()->optional()->countryCode(),
            'metadata' => ['source' => 'manual'],
            'verified_at' => null,
            'expires_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function forUserProfile(UserProfile $userProfile): static
    {
        return $this->state(fn (): array => [
            'user_profile_id' => $userProfile->id,
        ]);
    }
}
