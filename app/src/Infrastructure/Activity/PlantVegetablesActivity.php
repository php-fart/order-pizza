<?php

declare(strict_types=1);

namespace App\Infrastructure\Activity;

use Ramsey\Uuid\UuidInterface;

final readonly class PlantVegetablesActivity implements \App\Endpoint\Temporal\Activity\PlantVegetablesActivity
{
    public function __construct(
    )
    {
    }

    public function plantVegetables(string $name): UuidInterface
    {

    }

    public function isReady(UuidInterface $uuid): bool
    {

    }
}
