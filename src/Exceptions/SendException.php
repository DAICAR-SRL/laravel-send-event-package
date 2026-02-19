<?php

namespace Daicar\EventSender\Laravel\Exceptions;

class SendException extends \Exception
{
    public function __construct(string $filePath, array $eventAttributes, ?\Throwable $previous = null)
    {
        $message = sprintf(
            'Error sending file "%s" with attributes "%s"',
            $filePath,
            json_encode($eventAttributes)
        );

        parent::__construct($message, $previous ? $previous->getCode() : 0, $previous);
    }
}
