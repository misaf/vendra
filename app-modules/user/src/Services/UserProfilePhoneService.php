<?php

declare(strict_types=1);

namespace App\Models\User\Services;

use Propaganistas\LaravelPhone\PhoneNumber;

final class UserProfilePhoneService
{
    public static function phoneE164(string $country, PhoneNumber $phone)
    {
        return phone($phone->__toString(), $country)->formatE164();
    }

    public static function phoneNational(string $country, PhoneNumber $phone)
    {
        return preg_replace('/[^0-9]/', '', phone($phone->__toString(), $country)->formatNational());
    }

    public static function phoneNormalized(PhoneNumber $phone)
    {
        return preg_replace('/[^0-9]/', '', $phone->__toString());
    }
}
