<?php

namespace HCC\Events\Queue;

use HCC\Events\Queue\Interfaces\QueueEngineInterface;
use HCC\Events\Interfaces\JobInterface;

class InMemoryQueueEngine implements QueueEngineInterface
{
    private array $queue = [];

    public function push(JobInterface $job): void
    {
        $this->queue[] = $job;
    }

    public function process(): void
    {
        while ($job = array_shift($this->queue)) {
            $job->handle();
        }
    }
}
