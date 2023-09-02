<?php

declare(strict_types=1);

namespace App\Endpoint\Temporal\Activity;

use Ramsey\Uuid\UuidInterface;
use Temporal\Activity\ActivityInterface;

#[ActivityInterface]
interface NotificationActivity
{
    public function notify(UuidInterface $customerUuid, string $message);
}
