<?php

use PHPUnit\Framework\TestCase;
use \PhpAmqpLib\Channel\AMQPChannel;
use \PhpAmqpLib\Message\AMQPMessage;
use HCC\Events\Interfaces\JobInterface;
use HCC\Events\Queue\RabbitMQQueueEngine;

class RabbitMQQueueEngineTest extends TestCase
{

    protected AMQPChannel $channel;
    public function setUp(): void
    {
        $this->channel = $this->createMock(AMQPChannel::class);
    }

    public function testPush(): void
    {
        $job = $this->createMock(JobInterface::class);
        $this->channel->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function ($msg) use ($job) {
                    return $msg instanceof AMQPMessage && $msg->getBody() === serialize($job);
                }),
                '',
                'event_queue'
            );

        (new RabbitMQQueueEngine($this->channel))->push($job);
    }

    public function testProcess(): void
    {
        $job = $this->createMock(JobInterface::class);
        $msg = $this->createMock(AMQPMessage::class);
        $msg->method('getBody')->willReturn(serialize($job));
        $msg->expects($this->once())->method('getBody');

        $this->channel->expects($this->once())->method('basic_consume')
            ->with('event_queue', '', false, true, false, false, $this->callback(function ($callback) use ($msg) {
                $callback($msg);
                return true;
            }));

        $queue = new RabbitMQQueueEngine($this->channel);
        $queue->push($job);
        $queue->process();
    }
}
