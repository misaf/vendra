<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Actions;

use Filament\Actions\Action;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Actions\Concerns\ProcessesPayout;

final class ProcessPayoutTableAction extends Action
{
    use ProcessesPayout;
}
