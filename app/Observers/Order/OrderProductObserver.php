<?php

declare(strict_types=1);

namespace App\Observers\Order;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class OrderProductObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Order\OrderProduct $orderProduct): void {}

    public function deleted(\App\Models\Order\OrderProduct $orderProduct): void {}

    public function forceDeleted(\App\Models\Order\OrderProduct $orderProduct): void {}

    public function restored(\App\Models\Order\OrderProduct $orderProduct): void {}

    public function updated(\App\Models\Order\OrderProduct $orderProduct): void {}
}
