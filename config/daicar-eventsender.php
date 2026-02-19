<?php

return [
    'driver' => env('DAICAR_EVENT_SENDER_DRIVER', 'sqs'),

    'drivers' => [

        'sqs' => [
            'region' => env('DAICAR_EVENT_SENDER_SQS_REGION', 'eu-west-1'),
            'version' => 'latest',
            'credentials' => [
                'key' => env('DAICAR_EVENT_SENDER_SQS_KEY'),
                'secret' => env('DAICAR_EVENT_SENDER_SQS_SECRET'),
            ],
            'queue_url' => env('DAICAR_EVENT_SENDER_SQS_QUEUE_URL'),
        ],

    ],

    'backup_directory' => env('DAICAR_EVENT_SENDER_BACKUP_PATH', '/app/daicar-eventsender'),
];
