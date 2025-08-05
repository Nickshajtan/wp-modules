<?php

use HCC\Events\Interfaces\JobInterface;
use HCC\Events\Queue\InMemoryQueueEngine;
use PHPUnit\Framework\TestCase;

class InMemoryQueueEngineTest extends TestCase
{
    public function testPushAddsJobsToQueue(): void
    {
        $job = $this->createMock(JobInterface::class);
        $engine = new InMemoryQueueEngine();
        $engine->push($job);
        $job->expects($this->once())->method('handle');
        $engine->process();
    }

    public function testProcessCallsAllJobsAndClearsQueue(): void
    {
        $job1 = $this->createMock(JobInterface::class);
        $job2 = $this->createMock(JobInterface::class);
        $engine = new InMemoryQueueEngine();
        $engine->push($job1);
        $engine->push($job2);
        $job1->expects($this->once())->method('handle');
        $job2->expects($this->once())->method('handle');
        $engine->process();
        $job1->expects($this->never())->method('handle');
        $job2->expects($this->never())->method('handle');
        $engine->process();
    }
}
