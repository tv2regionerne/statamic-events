<?php

namespace Tv2regionerne\StatamicEvents\Drivers;

use Illuminate\Support\Facades\Http;
use Statamic\Facades\Antlers;

class WebhookDriver extends AbstractDriver
{
    public function handle(array $config, string $eventName, $event): void
    {
        if (! ($config['url'] ?? false)) {
            return;
        }

        $request = Http::async(($config['async'] ?? false) ? true : false);

        // headers
        if (! is_array($config['headers'] ?? [])) {
            $config['headers'] = json_decode($config['headers'], true);
        }

        $headers = collect($config['headers'] ?? [])->mapWithKeys(fn ($row) => [$row['key'] => $row['value']]);

        if ($headers->count()) {
            $request->withHeaders($headers->all());
        }

        // authentication
        // none, basic, digest, token
        switch ($config['authentication_type'] ?? 'none') {
            case 'basic':
                $request->withBasicAuth($config['authentication_user'], $config['authentication_password']);
            break;

            case 'digest':
                $request->withDigestAuth($config['authentication_user'], $config['authentication_password']);
            break;

            case 'token':
                $request->withToken($config['authentication_token']);
            break;
        }

        // timeout
        if ($timeout = ($config['timeout'] ?? false)) {
            $request->timeout($timeout);
        }

        // retries
        if ($retries = ($config['retry_count'] ?? false)) {
            $request->retry($retries, $config['retry_wait']);
        }

        // payload?
        if ($payload = ($config['payload'] ?? false)) {

            if ($config['payload_antlers_parse'] ?? false) {
                $payload = Antlers::parse($payload, array_merge([
                        'trigger_event' => $event,
                    ], $data));
            }

            if ($config['payload_json_decode'] ?? false) {
                $payload = json_decode($payload, true);
            }

            $request->withBody($payload, $config['payload_content_type']);
        }

        // run the request
        $response = $request->{$config['method']}($config['url']);

        // if we have a response handler class specified then hand off to it
        if (($class = ($config['response_handler'] ?? false)) && class_exists($class)) {
            (new $class())->handle($config, $eventName, $event, $response);
        }
    }

    public function blueprintFields(): array
    {
        return [
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                'url' => [
                                    'handle' => 'url',
                                    'field' => [
                                        'display' => __('URL'),
                                        'type' => 'link',
                                        'required' => true,
                                        'listable' => 'hidden',
                                        'width' => 50,
                                    ],
                                ],

                                'method' => [
                                    'handle' => 'method',
                                    'field' => [
                                        'type' => 'select',
                                        'listable' => 'hidden',
                                        'options' => [
                                            'get' => __('GET'),
                                            'post' => __('POST'),
                                            'delete' => __('DELETE'),
                                            'patch' => __('PATCH'),
                                            'put' => __('PUT'),
                                        ],
                                        'default' => 'get',
                                        'width' => 25,
                                    ],
                                ],

                                'async' => [
                                    'handle' => 'async',
                                    'field' => [
                                        'display' => __('Blocking'),
                                        'type' => 'toggle',
                                        'width' => 25,
                                    ],
                                ],

                                'timeout' => [
                                    'handle' => 'timeout',
                                    'field' => [
                                        'display' => __('Timeout (ms)'),
                                        'type' => 'integer',
                                        'width' => 25,
                                        'default' => '500',
                                    ],
                                ],

                                'retry_count' => [
                                    'handle' => 'retry_count',
                                    'field' => [
                                        'display' => __('Retry attempts'),
                                        'type' => 'integer',
                                        'width' => 25,
                                        'default' => 0,
                                    ],
                                ],

                                'retry_wait' => [
                                    'handle' => 'retry_wait',
                                    'field' => [
                                        'display' => __('Retry after (ms)'),
                                        'type' => 'integer',
                                        'width' => 25,
                                        'default' => 2000,
                                    ],
                                ],

                                'response_handler' => [
                                    'handle' => 'response_handler',
                                    'field' => [
                                        'display' => __('Response handler'),
                                        'instructions' => __('Response will be passed to this class in the handle() method'),
                                        'instructions_position' => 'below',
                                        'type' => 'select',
                                        'listable' => 'hidden',
                                        'options' => array_merge([
                                            'none' => __('None'),
                                        ], collect(config('statamic-events.response_handlers'))->mapWithKeys(fn ($handler, $key) => [$key => $key])->all()),
                                        'default' => 'none',
                                    ],
                                ],

                            ],
                        ],
                    ],
                ],
                'headers' => [
                    'sections' => [
                        [
                            'fields' => [
                                'headers' => [
                                    'handle' => 'headers',
                                    'field' => [
                                        'display' => __('Headers'),
                                        'type' => 'grid',
                                        'fields' => [
                                            [
                                                'handle' => 'key',
                                                'field' => [
                                                    'type' => 'text',
                                                    'required' => true,
                                                    'display' => __('Key'),
                                                ],
                                            ],
                                            [
                                                'handle' => 'value',
                                                'field' => [
                                                    'type' => 'text',
                                                    'required' => true,
                                                    'display' => __('Value'),
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'authentication' => [
                    'sections' => [
                        [
                            'fields' => [
                                'authentication_type' => [
                                    'handle' => 'authentication_type',
                                    'field' => [
                                        'display' => __('Authentication Type'),
                                        'type' => 'select',
                                        'listable' => 'hidden',
                                        'options' => [
                                            'none' => __('None'),
                                            'basic' => __('Basic'),
                                            'digest' => __('Digest'),
                                            'token' => __('Token'),
                                        ],
                                        'default' => 'none',
                                    ],
                                ],

                                'authentication_user' => [
                                    'handle' => 'authentication_user',
                                    'field' => [
                                        'display' => __('User'),
                                        'type' => 'text',
                                        'validate' => [
                                            'required_unless:authentication_type,token,none'
                                        ]
                                    ],
                                ],

                                'authentication_password' => [
                                    'handle' => 'authentication_password',
                                    'field' => [
                                        'display' => __('Password'),
                                        'type' => 'text',
                                        'validate' => [
                                            'required_unless:authentication_type,token,none'
                                        ]
                                    ],
                                ],

                                'authentication_token' => [
                                    'handle' => 'authentication_token',
                                    'field' => [
                                        'display' => __('Token'),
                                        'type' => 'text',
                                        'validate' => [
                                            'required_if:authentication_type,token'
                                        ]
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'payload' => [
                    'sections' => [
                        [
                            'fields' => [
                                'payload' => [
                                    'handle' => 'payload',
                                    'field' => [
                                        'display' => __('Body'),
                                        'type' => 'textarea',
                                        'validate' => [
                                            'required_unless:method,get,delete'
                                        ]
                                    ],
                                ],

                                'payload_content_type' => [
                                    'handle' => 'payload_content_type',
                                    'field' => [
                                        'display' => __('Content Type'),
                                        'type' => 'text',
                                        'validate' => [
                                            'required_if:payload'
                                        ]
                                    ],
                                ],

                                'payload_antlers_parse' => [
                                    'handle' => 'payload_antlers_parse',
                                    'field' => [
                                        'display' => __('Parse using Antlers'),
                                        'type' => 'toggle',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return __('Webhook');
    }
}
