<?php

namespace Daicar\EventSender\Laravel\Exceptions;

class BackupException extends \Exception
{
    public function __construct(string $filePath, array $eventAttributes, ?\Throwable $previous = null)
    {
        $message = sprintf(
            'Error backing up file "%s" with attributes "%s"; error: %s',
            $filePath,
            json_encode($eventAttributes),
            $previous ? $previous->getMessage() : ''
        );

        parent::__construct($message, $previous ? $previous->getCode() : 500, $previous);
    }
}
