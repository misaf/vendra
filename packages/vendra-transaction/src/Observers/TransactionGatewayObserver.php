<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Observers;

use Misaf\VendraSupport\Observers\Concerns\MaintainsSingleActiveDefault;

final class TransactionGatewayObserver
{
    use MaintainsSingleActiveDefault;
}
