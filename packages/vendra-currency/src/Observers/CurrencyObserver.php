<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Observers;

use Misaf\VendraSupport\Observers\Concerns\MaintainsSingleActiveDefault;

final class CurrencyObserver
{
    use MaintainsSingleActiveDefault;
}
