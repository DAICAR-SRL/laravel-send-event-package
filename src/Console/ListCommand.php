<?php

namespace Daicar\EventSender\Laravel\Console;

use Illuminate\Console\Command;
use Daicar\EventSender\Laravel\EventSender;

/**
 * @codeCoverageIgnore
 */
class ListCommand extends Command
{
    protected $signature = 'event-sender:list';
    protected $description = 'List pending buffered events';

    public function handle(EventSender $sender): void
    {
        $files = $sender->pending();

        if (empty($files)) {
            $this->info('No pending events.');
            return;
        }

        foreach ($files as $file) {
            $this->line($file);
        }

        $this->info('Total: '.count($files));
    }
}


