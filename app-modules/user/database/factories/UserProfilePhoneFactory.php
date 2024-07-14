<?php

declare(strict_types=1);

namespace Termehsoft\User\Database\Factories;

use App\Services\UserProfilePhoneService;
use App\Support\Enums\UserProfilePhoneStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\User\Models\UserProfile;
use Termehsoft\User\Models\UserProfilePhone;

final class UserProfilePhoneFactory extends Factory
{
    protected $model = UserProfilePhone::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $country = $this->faker->countryISOAlpha3();

        $phone = $this->faker->phoneNumber();

        return [
            'user_profile_id'  => UserProfile::class,
            'country'          => $this->faker->countryISOAlpha3(),
            'phone'            => $phone,
            'phone_normalized' => UserProfilePhoneService::phoneNormalized($phone),
            'phone_national'   => UserProfilePhoneService::phoneNational($country, $phone),
            'phone_e164'       => UserProfilePhoneService::phoneE164($country, $phone),
            'status'           => $this->faker->randomElement(UserProfilePhoneStatusEnum::cases()),
            'verified_at'      => $this->faker->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
