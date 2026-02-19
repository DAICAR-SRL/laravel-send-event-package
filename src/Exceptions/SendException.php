<?php

namespace Daicar\EventSender\Laravel\Exceptions;

class SendException extends \Exception
{
    /**
     * @param string $filePath
     * @param array<string, mixed> $eventAttributes
     * @param \Throwable|null $previous
     */
    public function __construct(string $filePath, array $eventAttributes, ?\Throwable $previous = null)
    {
        $message = sprintf(
            'Error sending file "%s" with ATTRIBUTES "%s"',
            $filePath,
            json_encode($eventAttributes)
        );

        parent::__construct($message, $previous ? $previous->getCode() : 0, $previous);
    }
}
