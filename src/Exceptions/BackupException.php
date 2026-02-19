<?php

namespace Daicar\EventSender\Laravel\Exceptions;

class BackupException extends \Exception
{
    /**
     * @param string $filePath
     * @param array<string, mixed> $eventAttributes
     * @param \Throwable|null $previous
     */
    public function __construct(string $filePath, array $eventAttributes, ?\Throwable $previous = null)
    {
        $message = sprintf(
            'Error backing up file "%s" with ATTRIBUTES "%s", ERROR MESSAGE: %s',
            $filePath,
            json_encode($eventAttributes),
            $previous ? $previous->getMessage() : ''
        );

        parent::__construct($message, $previous ? $previous->getCode() : 500, $previous);
    }
}
