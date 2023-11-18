<?php

namespace Tv2regionerne\StatamicEvents\Drivers;

use Illuminate\Support\Facades\Http;
use Statamic\Facades\Antlers;

class WebhookDriver extends AbstractDriver
{
    public function handle($handler, string $event, array $data) : void
    {
        if (! $handler->url) {
            return;
        }
            
        $request = ($handler->async ? Http : Http::async);
            
        // payload?
        if ($handler->payload) {

            $payload = Antlers::parse($handler->payload, array_merge([
                    'trigger_event' => $event, 
                ], $data));
                
            $request->withBody($payload, $handler->content_type);
        }
        
        $request->{$handler->method}($url);
    }
    
    public function blueprintFields(): array
    {
        return [];
    }
}
