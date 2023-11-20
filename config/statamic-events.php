<?php

use Tv2regionerne\StatamicEvents\Drivers;

return [

    'drivers' => [
        'audit' => Drivers\AuditDriver::class,
        'webhook' => Drivers\WebhookDriver::class,
    ],

    'response_handlers' => [
        // define any response handlers
        // e.g. 'my_handler' => App\ResponseHandlers\MyHandler::class
        // they should have a handle method, which accepts 4 params
        // handle(array $config, string $eventName, $event, Response $response)
    ],
];
