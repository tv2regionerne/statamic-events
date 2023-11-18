<?php

namespace Tv2regionerne\StatamicEvents\Drivers;

use Illuminate\Support\Facades\Log;
use Statamic\Facades\Antlers;

class AuditDriver extends AbstractDriver
{
    public function handle($handler, string $event, array $data) : void
    {
        $message = Antlers::parse($handler->message, array_merge([
                'trigger_event' => $event, 
            ], $data));
            
        Log::{$handler->level ?? 'info'}($message);    
    }
    
    public function blueprintFields(): array
    {
        return [
            'level' => [
                'type' => 'text',
                'handle' => 'level',
                'required' => true
            ],
            'message' => [
                'type' => 'textarea',
                'handle' => 'message',
                'required' => true
            ],
        ];
    }
}
