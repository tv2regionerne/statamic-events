<?php

namespace Tv2regionerne\StatamicEvents\Drivers;

use Illuminate\Support\Facades\Log;
use Statamic\Facades\Antlers;
use Tv2regionerne\StatamicEvents\Models\Execution;

class AuditDriver extends AbstractDriver
{
    public function handle(array $config, string $eventName, $event, Execution $execution): void
    {
        try {
            $message = Antlers::parse($config['message'], array_merge([
                'trigger_event' => $eventName,
            ], get_object_vars($event)));

            if ($channel = $config['channel'] ?? null) {
                Log::channel($config['channel'] ?? null)->{$config['level'] ?? 'info'}((string) $message);
            } else {
                Log::{$config['level'] ?? 'info'}((string) $message);
            }

            $execution->log(__('Logged message: :message', ['message' => $message]), [
                'level' => $config['level'],
            ]);

            // if we have a response handler class specified then hand off to it
            if (($class = ($config['response_handler'] ?? false)) && class_exists($class)) {
                $execution->log(__('Passing response to handler: :class', ['class' => $class]));

                $response = (new $class())->handle($config, $eventName, $event, $execution);

                $execution->log(__('Received response from handler'));
            }

            $execution->complete($response ?? '');
        } catch (\Throwable $e) {
            $execution->fail($e->getMessage());
        }
    }

    public function blueprintFields(): array
    {
        return [
            'channel' => [
                'handle' => 'channel',
                'field' => [
                    'display' => __('Channel'),
                    'type' => 'select',
                    'required' => false,
                    'listable' => 'hidden',
                    'options' => collect(config('logging.channels'))->sortKeys()->keys()->all(),
                ],
            ],
            'level' => [
                'handle' => 'level',
                'field' => [
                    'display' => __('Level'),
                    'type' => 'select',
                    'required' => true,
                    'listable' => 'hidden',
                    'options' => [
                        'alert' => __('Alert'),
                        'critical' => __('Critical'),
                        'debug' => __('Debug'),
                        'emergency' => __('Emergency'),
                        'error' => __('Error'),
                        'info' => __('Info'),
                        'notice' => __('Notice'),
                        'warning' => __('Warning'),
                    ],
                ],
            ],
            'message' => [
                'handle' => 'message',
                'field' => [
                    'display' => __('Message'),
                    'type' => 'textarea',
                    'required' => true,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return __('Audit');
    }
}
