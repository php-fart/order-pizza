<?php

declare(strict_types=1);

namespace App\Endpoint\Temporal\Activity;

use Ramsey\Uuid\UuidInterface;
use Temporal\Activity\ActivityInterface;

#[ActivityInterface]
interface PlantVegetablesActivity
{
    #[ActivityMethod]
    public function plantVegetables(string $name): UuidInterface;

    #[ActivityMethod]
    public function isReady(UuidInterface $uuid): bool;
}
