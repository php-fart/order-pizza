<?php

declare(strict_types=1);

namespace App\Infrastructure\Activity;

final class OrderActivity implements \App\Endpoint\Temporal\Activity\OrderActivity
{
    public function calculatePrice(string $name): int
    {
        return random_int(0, 1000000);
    }
}
