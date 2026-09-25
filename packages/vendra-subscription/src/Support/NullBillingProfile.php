<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Support;

use Illuminate\Support\Facades\Config;
use Misaf\VendraSubscription\Contracts\BillingProfile;

final class NullBillingProfile implements BillingProfile
{
    public function taxRate(): int
    {
        return 0;
    }

    public function taxLabel(): string
    {
        return 'VAT';
    }

    public function seller(): array
    {
        return ['name' => Config::string('app.name'), 'address' => null, 'tax_id' => null];
    }
}
