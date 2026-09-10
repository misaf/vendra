<?php

declare(strict_types=1);

namespace Misaf\VendraDocument\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraDocument\Models\Document;
use Misaf\VendraUserProfile\Models\UserProfile;

/** @extends Factory<Document> */
#[UseModel(Document::class)]
final class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_profile_id' => UserProfile::factory(),
            'type' => fake()->randomElement(['identity', 'passport', 'license', 'tax', 'utility', 'other']),
            'issuing_country_code' => fake()->countryCode(),
            'number' => fake()->optional()->bothify('DOC-########'),
            'issued_at' => fake()->optional()->dateTimeBetween('-5 years', '-1 year'),
            'expires_at' => fake()->optional()->dateTimeBetween('+1 month', '+5 years'),
            'verified_at' => null,
            'metadata' => [],
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
