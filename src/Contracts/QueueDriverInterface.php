<?php

namespace Daicar\EventSender\Laravel\Contracts;

interface QueueDriverInterface
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $attributes
     * @return string
     */
    public function send(array $payload, array $attributes): string;
}
