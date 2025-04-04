<?php

namespace HCC\Events\Interfaces;

interface JobInterface
{
    public function handle(): void;
}
