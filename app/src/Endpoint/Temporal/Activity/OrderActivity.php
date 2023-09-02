<?php

declare(strict_types=1);

namespace App\Endpoint\Temporal\Activity;

use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface(prefix: 'Order.')]
interface OrderActivity
{
    #[ActivityMethod('calculatePrice')]
    public function calculatePrice(string $name): int;
}
