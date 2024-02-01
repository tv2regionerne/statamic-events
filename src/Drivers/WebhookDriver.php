<?php

namespace Tv2regionerne\StatamicEvents\Drivers;

use Illuminate\Support\Facades\Http;
use Statamic\Facades\Antlers;
use Tv2regionerne\StatamicEvents\Models\Execution;

class WebhookDriver extends AbstractDriver
{
    public function handle(array $config, string $eventName, $event, Execution $execution): void
    {
        try {
            if (! ($config['url'] ?? false)) {
                throw new \Exception(__('No url specified in handler'));
            }

            $request = Http::async(false);

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

            // get all public properties on the event
            $data = array_merge([
                'trigger_event' => $eventName,
            ], get_object_vars($event));

            $payload = false;

            if (! in_array(strtoupper($config['method']), ['GET', 'DELETE'])) {
                $payload = ($config['payload'] ?? $data);
            }

            // payload?
            if ($payload) {

                if ($config['payload_antlers_parse'] ?? false) {
                    $payload = Antlers::parse($payload, array_merge([
                        'trigger_event' => $eventName,
                    ], $data));
                }

                if ($config['payload_json_decode'] ?? false) {
                    $payload = json_decode($payload, true);
                }

                $request->withBody($payload, $config['payload_content_type'] ?? 'application/x-www-form-urlencoded');
            }

            $execution->log(__('Sending request to :url', ['url' => $config['url'], 'payload' => $payload]));

            // run the request
            $response = $request->{$config['method']}($config['url']);

            $execution->log(__('Response received'), [
                'response' => $response->getBody(),
            ]);

            // if we have a response handler class specified then hand off to it
            if (($class = ($config['response_handler'] ?? false)) && class_exists($class)) {
                $execution->log(__('Passing response to handler: :class', ['class' => $class]));

                $response = (new $class())->handle($config, $eventName, $event, $execution, $response);

                $execution->log(__('Received response from handler'));
            }

            $execution->complete($response->getBody());

        } catch (\Throwable $e) {
            $execution->fail($e->getMessage());
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
                                        'type' => 'text',
                                        'input_type' => 'url',
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
                                        ], collect($this->get('response_handlers', []))->mapWithKeys(fn ($handler, $key) => [$key => $key])->all()),
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
                                            'required_unless:authentication_type,token,none',
                                        ],
                                        'hide_when' => [
                                            'authentication_type' => 'contains_any token,none',
                                        ],
                                    ],
                                ],

                                'authentication_password' => [
                                    'handle' => 'authentication_password',
                                    'field' => [
                                        'display' => __('Password'),
                                        'type' => 'text',
                                        'validate' => [
                                            'required_unless:authentication_type,token,none',
                                        ],
                                        'hide_when' => [
                                            'authentication_type' => 'contains_any token,none',
                                        ],
                                    ],
                                ],

                                'authentication_token' => [
                                    'handle' => 'authentication_token',
                                    'field' => [
                                        'display' => __('Token'),
                                        'type' => 'text',
                                        'validate' => [
                                            'required_if:authentication_type,token',
                                        ],
                                        'show_when_any' => [
                                            'authentication_type' => 'is token',
                                        ],
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
                                            'required_unless:method,get,delete',
                                        ],
                                    ],
                                ],

                                'payload_content_type' => [
                                    'handle' => 'payload_content_type',
                                    'field' => [
                                        'display' => __('Content Type'),
                                        'type' => 'text',
                                        'validate' => [
                                            'required_with:payload',
                                        ],
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
