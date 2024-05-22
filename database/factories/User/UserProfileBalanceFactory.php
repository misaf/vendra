<?php

declare(strict_types=1);

namespace Database\Factories\User;

use App\Models\Currency\Currency;
use App\Models\User\UserProfile;
use App\Models\User\UserProfileBalance;
use Illuminate\Database\Eloquent\Factories\Factory;

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
