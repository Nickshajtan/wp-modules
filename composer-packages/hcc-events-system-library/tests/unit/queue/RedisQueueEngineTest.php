<?php

use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;
use HCC\Events\Interfaces\JobInterface;
use HCC\Events\Queue\RedisQueueEngine;

class RedisQueueEngineTest extends TestCase
{
    protected ClientInterface $client;

    public function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
    }

    public function testPush(): void
    {
        $job = $this->createMock(JobInterface::class);
        $this->client->expects($this->once())
            ->method('__call')
            ->with('lpush', ['event_queue', [serialize($job)]]);

        (new RedisQueueEngine($this->client))->push($job);
    }

    public function testProcess(): void
    {
        $job = $this->createMock(JobInterface::class);
        $queue = new RedisQueueEngine($this->client);
        $queue->push($job);

        $this->client->expects($this->atLeastOnce())
            ->method('__call')
            ->with('rpop', ['event_queue']);
        $queue->process();
    }
}
