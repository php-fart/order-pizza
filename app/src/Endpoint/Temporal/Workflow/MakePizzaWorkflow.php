<?php

declare(strict_types=1);

namespace App\Endpoint\Temporal\Workflow;

use Ramsey\Uuid\UuidInterface;
use Temporal\Workflow\SignalMethod;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
interface MakePizzaWorkflow
{
    #[WorkflowMethod]
    public function execute(InputDto $input);

    #[SignalMethod]
    public function vegesAreGrown();
}

class InputDto {
    /**
     * @param non-empty-string $name
     * @param list<non-empty-string> $ingridients
     */
    public function __construct(
        public string $name,
        public UuidInterface $customerUuid,
        public array $ingridients = [],
    ) {
    }
}
