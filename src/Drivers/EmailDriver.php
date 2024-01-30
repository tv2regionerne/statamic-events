<?php

namespace Tv2regionerne\StatamicEvents\Drivers;

use Illuminate\Support\Facades\Mail;
use Tv2regionerne\StatamicEvents\Mail\PlainMail;
use Tv2regionerne\StatamicEvents\Models\Execution;

class EmailDriver extends AbstractDriver
{
    public function handle(array $config, string $eventName, $event, Execution $execution): void
    {
        try {
            if (! ($config['to'] ?? false)) {
                throw new \Exception(__('No to addresses specified in handler'));
            }

            $mail = Mail::mailer($config['mailer'] ?? null);

            $mail->to($config['to']);

            if ($cc = $config['cc'] ?? []) {
                $mail->cc($cc);
            }

            if ($bcc = $config['bcc'] ?? []) {
                $mail->bcc($bcc);
            }

            if ($from = $config['from'] ?? []) {
                Mail::alwaysFrom($from['email'], $from['name']);
            }

            $execution->log(__('Sending email to :to', ['to' => implode(', ', $config['to'])]));

            $sent = false;
            if ($mailable = $config['mailable'] ?? []) {
                if (class_exists($mailable)) {
                    $mail->send(new $mailable);
                    $sent = true;
                }
            }

            if (! $sent) {
                $mail->send(new PlainMail($config['subject'] ?? '', $config['text'] ?? '', $config['html'] ?? ''));
            }

            // if we have a response handler class specified then hand off to it
            if (($class = ($config['response_handler'] ?? false)) && class_exists($class)) {
                $execution->log(__('Passing response to handler: :class', ['class' => $class]));

                $response = (new $class())->handle($config, $eventName, $event, $execution);

                $execution->log(__('Received response from handler'));
            }

            $execution->complete($response ?? __('Mail sent'));

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
                                'to' => [
                                    'handle' => 'to',
                                    'field' => [
                                        'display' => __('To'),
                                        'type' => 'grid',
                                        'mode' => 'stacked',
                                        'fields' => [
                                            [
                                                'handle' => 'email',
                                                'field' => [
                                                    'display' => __('Email'),
                                                    'type' => 'text',
                                                    'required' => true,
                                                    'listable' => 'hidden',
                                                ],
                                            ],
                                        ],
                                        'required' => true,
                                        'listable' => 'hidden',
                                    ],
                                ],

                                'subject' => [
                                    'handle' => 'subject',
                                    'field' => [
                                        'display' => __('Subject'),
                                        'type' => 'text',
                                        'required' => true,
                                        'listable' => 'hidden',
                                    ],
                                ],

                                'cc' => [
                                    'handle' => 'cc',
                                    'field' => [
                                        'display' => __('CC'),
                                        'type' => 'grid',
                                        'mode' => 'stacked',
                                        'fields' => [
                                            [
                                                'handle' => 'email',
                                                'field' => [
                                                    'display' => __('Email'),
                                                    'type' => 'text',
                                                    'required' => true,
                                                    'listable' => 'hidden',
                                                ],
                                            ],
                                        ],
                                        'required' => true,
                                        'listable' => 'hidden',
                                    ],
                                ],

                                'bcc' => [
                                    'handle' => 'bcc',
                                    'field' => [
                                        'display' => __('BCC'),
                                        'type' => 'grid',
                                        'mode' => 'stacked',
                                        'fields' => [
                                            [
                                                'handle' => 'email',
                                                'field' => [
                                                    'display' => __('Email'),
                                                    'type' => 'text',
                                                    'required' => true,
                                                    'listable' => 'hidden',
                                                ],
                                            ],
                                        ],
                                        'required' => true,
                                        'listable' => 'hidden',
                                    ],
                                ],

                                'text' => [
                                    'handle' => 'text',
                                    'field' => [
                                        'display' => __('Plain Text'),
                                        'type' => 'textarea',
                                        'required' => true,
                                        'listable' => 'hidden',
                                    ],
                                ],

                                'html' => [
                                    'handle' => 'html',
                                    'field' => [
                                        'display' => __('HTML'),
                                        'type' => 'textarea',
                                        'required' => true,
                                        'listable' => 'hidden',
                                    ],
                                ],

                                'mailable' => [
                                    'handle' => 'mailable',
                                    'field' => [
                                        'display' => __('Mailable'),
                                        'type' => 'select',
                                        'listable' => 'hidden',
                                        'options' => collect(glob(base_path('App/Mail/*.php')))
                                            ->mapWithKeys(function ($file) {
                                                $fqcn = $namespace.'\\'.Str::of($file)->after('/src/')->before('.php')->replace('/', '\\');
                                                return [$fqcn => Str::of($file)->afterLast('/')->before('.php')];
                                            })
                                            ->all(),
                                    ],
                                ],

                                'mailer' => [
                                    'handle' => 'mailer',
                                    'field' => [
                                        'display' => __('Mailer'),
                                        'type' => 'select',
                                        'listable' => 'hidden',
                                        'options' => collect(config('mail.mailers'))->sortKeys()->keys()->all(),
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
        return __('Email');
    }
}
