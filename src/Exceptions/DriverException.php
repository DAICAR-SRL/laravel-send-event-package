<?php

namespace Daicar\EventSender\Laravel\Exceptions;

use Exception;
use Throwable;

class DriverException extends Exception
{
    /**
     * @param string $driver
     * @param array<string, mixed> $eventPayload
     * @param array<string, mixed> $eventAttributes
     * @param Throwable|null $previous
     */
    public function __construct(string $driver, array $eventPayload, array $eventAttributes, ?Throwable $previous = null)
    {
        $errorMessage = 'unknown';
        $errorCode = 500;

        if ($previous && $previous instanceof Throwable) {
            $errorMessage = $previous->getMessage();
            $errorCode = $previous->getCode();
        }

        $message = sprintf(
            'Error sending event through "%s" driver with PAYLOAD "%s" and ATTRIBUTES "%s", ERROR MESSAGE: %s',
            $driver,
            json_encode($eventPayload),
            json_encode($eventAttributes),
            $errorMessage
        );

        parent::__construct($message, $errorCode, $previous);
    }
}
