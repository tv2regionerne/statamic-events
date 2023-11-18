<?php

use Tv2regionerne\StatamicEvents\Drivers;

return [
    
    'drivers' => [
        'audit' => Drivers\AuditDriver::class,
        'webhook' => Drivers\WebhookDriver::class,
    ],

    'events' => [
//        'Statamic\Events\EntrySaving' => [
//            [
//                'blocking' => true,
//                'type' => 'webhook',
//                'endpoint' => 'http://167.235.59.8/webhook/1a649b22-4db8-4571-9a89-b8f7492747b8',
//                //'endpoint' => 'http://167.235.59.8/webhook-test/1a649b22-4db8-4571-9a89-b8f7492747b8',
//                'payload' => function($entry) {
//                    return [
//                        'input' => json_encode($entry->get('content'))
//                    ];
//
//                },
//                'handler' => function($entry, $data) {
//                    foreach ($data as $key => $value) {
//                        dd($key, $value);
//                        $entry->data($key, $value);
//                    }
//                }
//            ]
//        ]
    ],

];
