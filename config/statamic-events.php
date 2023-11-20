<?php

use Tv2regionerne\StatamicEvents\Drivers;

return [

    'drivers' => [
        'audit' => Drivers\AuditDriver::class,
        'webhook' => Drivers\WebhookDriver::class,
    ],

];
