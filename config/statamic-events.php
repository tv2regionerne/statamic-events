<?php

use Illuminate\Support\Facades\Http;
use Statamic\Eloquent\Entries\Entry;

return [

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
