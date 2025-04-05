<?php

namespace HCC\Events\Queue\Interfaces;

use HCC\Events\Interfaces\JobInterface;

interface QueueEngineInterface
{
    public function push(JobInterface $job): void;

    public function process(): void;
}
