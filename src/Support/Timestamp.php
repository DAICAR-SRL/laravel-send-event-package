<?php

namespace Daicar\EventSender\Laravel\Support;

use Illuminate\Support\Carbon;

class Timestamp
{
    public static function nowUtcMillis(): string
    {
        $micro = microtime(true);
        $milliseconds = sprintf('%03d', ($micro - floor($micro)) * 1000);

        return gmdate('Y-m-d\TH:i:s', (int)$micro)
            . '.' . $milliseconds . 'Z';
    }

    public static function utcMillisFromCarbon(Carbon $dateTime): string
    {
        return $dateTime->format('Y-m-d\TH:i:s.v\Z');
    }
}
