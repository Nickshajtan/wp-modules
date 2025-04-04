<?php

namespace HCC\Events\Queue;

use HCC\Events\Queue\Interfaces\QueueEngineInterface;
use HCC\Events\Interfaces\JobInterface;
use \Predis\Client;

class RedisQueueEngine implements QueueEngineInterface
{
    protected const KEY = 'event_queue';

    public function __construct(readonly private Client $redis) {}

    public function push(JobInterface $job): void
    {
        $this->redis->lpush(static::KEY, serialize($job));
    }

    public function process(): void
    {
        while ($jobData = $this->redis->rpop(static::KEY)) {
            $job = unserialize($jobData);
            $job->handle();
        }
    }
}
