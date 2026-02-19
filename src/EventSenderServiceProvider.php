<?php

namespace Daicar\EventSender\Laravel;

use Aws\Sqs\SqsClient;
use Daicar\EventSender\Laravel\Console\ListCommand;
use Daicar\EventSender\Laravel\Console\RetryCommand;
use Daicar\EventSender\Laravel\Drivers\SqsDriver;
use Daicar\EventSender\Laravel\EventSender;
use Daicar\EventSender\Laravel\Support\BackupStore;
use Illuminate\Support\ServiceProvider;

error_reporting(E_ALL ^ E_USER_DEPRECATED);
class EventSenderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/daicar-eventsender.php',
            'event-sender'
        );

        $this->app->singleton(EventSender::class, function ($app) {
            $config = config('event-sender');
            $driverName = $config['driver'];

            if ($driverName === 'sqs') {
                $driverConfig = $config['drivers']['sqs'];

                $client = new SqsClient([
                    'region' => $driverConfig['region'],
                    'version' => $driverConfig['version'],
                    'credentials' => $driverConfig['credentials'],
                    'suppress_php_deprecation_warning' => true
                ]);

                $driver = new SqsDriver(
                    $client,
                    $driverConfig['queue_url']
                );

            } else {
                throw new \InvalidArgumentException(
                    "Driver [$driverName] not supported."
                );
            }

            $backupPath = $app->storagePath(
                $config['backup_directory']
            );

            return new EventSender(
                $driver,
                new BackupStore($backupPath)
            );
        });

        /**
         * @example
         * app(\Daicar\EventSender\Laravel\EventSender::class)
         *   ->send('mattia-chat', [
         *     'userId' => 10,
         *     'action' => 'created',
         *     ...
         *   ]);
         */
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/daicar-eventsender.php' =>
                $this->app->configPath('daicar-eventsender.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                RetryCommand::class,
                ListCommand::class,
            ]);
        }
    }
}
