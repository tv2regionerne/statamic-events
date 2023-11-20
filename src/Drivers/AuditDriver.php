<?php

namespace Tv2regionerne\StatamicEvents\Drivers;

use Illuminate\Support\Facades\Log;
use Statamic\Facades\Antlers;

class AuditDriver extends AbstractDriver
{
    public function handle(array $config, string $eventName, $event): void
    {
        $message = Antlers::parse($config['message'], array_merge([
                'trigger_event' => $eventName, 
            ], get_object_vars($event)));
            
        Log::{$handler['level'] ?? 'info'}($message);   
    }
    
    public function blueprintFields(): array
    {
        return [
            'level' => [
                'handle' => 'level',
                'field' => [
                    'type' => 'select',
                    'handle' => 'level',
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
                    'type' => 'textarea',
                    'handle' => 'message',
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
