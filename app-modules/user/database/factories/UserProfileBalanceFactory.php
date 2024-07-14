<?php

declare(strict_types=1);

namespace Termehsoft\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Currency\Models\Currency;
use Termehsoft\User\Models\UserProfile;
use Termehsoft\User\Models\UserProfileBalance;

final class UserProfileBalanceFactory extends Factory
{
    protected $model = UserProfileBalance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_profile_id' => UserProfile::class,
            'currency_id'     => Currency::class,
            'status'          => $this->faker->boolean(),
        ];
    }
}
