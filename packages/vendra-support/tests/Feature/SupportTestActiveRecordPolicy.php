<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Illuminate\Contracts\Auth\Authenticatable;

final class SupportTestActiveRecordPolicy
{
    public static bool $allowsUpdate = false;

    public function update(?Authenticatable $user, SupportTestActiveRecord $record): bool
    {
        return self::$allowsUpdate;
    }
}
