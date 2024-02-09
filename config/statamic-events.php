<?php

use Tv2regionerne\StatamicEvents\Drivers;

return [
    'drivers' => [
        'audit' => [
            'driver' => Drivers\AuditDriver::class,
            'response_handlers' => [],
        ],
        'email' => [
            'driver' => Drivers\EmailDriver::class,
            'response_handlers' => [],
        ],
        'webhook' => [
            'driver' => Drivers\WebhookDriver::class,
            'response_handlers' => [],
        ],
    ],

    'events' => [
        'Statamic' => 'vendor/statamic/cms/src/Events',
        'StatamicEvents' => 'vendor/tv2regionerne/statamic-events/src/Events',
    ],

    'queue_name' => 'default',
];
