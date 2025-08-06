<?php

namespace HCC\Events\Queue;

use HCC\Events\Queue\Interfaces\QueueEngineInterface;
use HCC\Events\Interfaces\JobInterface;
use \PhpAmqpLib\Message\AMQPMessage;
use \PhpAmqpLib\Channel\AMQPChannel;
class RabbitMQQueueEngine implements QueueEngineInterface
{
    private AMQPChannel $channel;

    protected const KEY = 'event_queue';

    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
    }

    public function push(JobInterface $job): void
    {
        $msg = new AMQPMessage(serialize($job));
        $this->channel->basic_publish($msg, '', static::KEY);
    }

    public function process(int $maxIterations = 100): void
    {
        $callback = function (AMQPMessage $msg) {
            $job = unserialize($msg->getBody());
            if ($job instanceof JobInterface) {
                $job->handle();
            }
        };

        $this->channel->basic_consume(static::KEY, '', false, true, false, false, $callback);
        $iterations = 0;
        while ( count($this->channel->callbacks) > 0 && $iterations++ < $maxIterations ) {
            $this->channel->wait();
        }
    }
}