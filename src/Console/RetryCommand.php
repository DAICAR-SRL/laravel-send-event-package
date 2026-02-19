<?php

namespace Daicar\EventSender\Laravel\Console;

use Illuminate\Console\Command;
use Daicar\EventSender\Laravel\EventSender;

/**
 * @codeCoverageIgnore
 */
class RetryCommand extends Command
{
    protected $signature = 'event-sender:retry';
    protected $description = 'Retry sending buffered events';

    public function handle(EventSender $sender): void
    {
        $sender->retry();
        $this->info('Retry completed.');
    }
}

