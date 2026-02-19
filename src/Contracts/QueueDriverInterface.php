<?php

namespace Daicar\EventSender\Laravel\Contracts;

interface QueueDriverInterface
{
    public function send(array $payload, array $attributes);
}
