<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Enums;

enum StorefrontReconciliationOutcome: string
{
    case InSync = 'in sync';
    case Started = 'started';
    case Stopped = 'stopped';
    case Deployed = 'deployed';
    case Redeployed = 'redeployed';
}
