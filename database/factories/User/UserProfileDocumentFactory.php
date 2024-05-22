<?php

declare(strict_types=1);

namespace Database\Factories\User;

use App\Models\User\UserProfile;
use App\Models\User\UserProfileDocument;
use App\Support\Enums\UserProfileDocumentStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

final class UserProfileDocumentFactory extends Factory
{
    protected $model = UserProfileDocument::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_profile_id' => UserProfile::class,
            'status'          => $this->faker->randomElement(UserProfileDocumentStatusEnum::cases()),
            'verified_at'     => $this->faker->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
