<?php

namespace HCC\Events\Queue;

use HCC\Events\Queue\Interfaces\QueueEngineInterface;
use HCC\Events\Interfaces\JobInterface;
use \PhpAmqpLib\Connection\AMQPStreamConnection;
use \PhpAmqpLib\Message\AMQPMessage;
class RabbitMQQueueEngine implements QueueEngineInterface
{
    private AMQPStreamConnection $connection;
    private \AMQPChannel $channel;

    protected const KEY = 'event_queue';

    public function __construct(AMQPStreamConnection $connection)
    {
        $this->connection = $connection;
        $this->channel = $this->connection->channel();
    }

    public function push(JobInterface $job): void
    {
        $msg = new AMQPMessage(serialize($job));
        $this->channel->basic_publish($msg, '', static::KEY);
    }

    public function process(): void
    {
        $callback = function (AMQPMessage $msg) {
            $job = unserialize($msg->getBody());
            $job->handle();
        };

        $this->channel->basic_consume(static::KEY, '', false, true, false, false, $callback);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }
}