<?php

namespace Daicar\EventSender\Laravel\Support;

class Timestamp
{
    public static function nowUtcMillis(): string
    {
        $micro = microtime(true);
        $milliseconds = sprintf('%03d', ($micro - floor($micro)) * 1000);

        return gmdate('Y-m-d\TH:i:s', (int)$micro)
            . '.' . $milliseconds . 'Z';
    }
}
