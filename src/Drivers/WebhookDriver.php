<?php

namespace Tv2regionerne\StatamicEvents\Drivers;

use Illuminate\Support\Facades\Http;
use Statamic\Facades\Antlers;

class WebhookDriver extends AbstractDriver
{
    public function handle(array $config, string $event, array $data): void
    {
        if (! $handler['url']) {
            return;
        }
            
        $request = ($handler['async'] ? Http : Http::async);
            
        // headers
        // -- make this a replicator/grid in the blueprintFields
        if ($headers = collect($handler['headers'])->mapWithKeys(fn ($row) => [$row['key'] => $row['value']])) {
            $request->withHeaders($headers);
        }
        
        // authentication
        // none basic, digest, token
        // https://laravel.com/docs/10.x/http-client#authentication
        switch ($handler['authentication_type']) {
            case 'basic':
                $request->withBasicAuth($handler['authentication_user'], $handler['authentication_password']);
            break;
            
            case 'digest':
                $request->withDigestAuth($handler['authentication_user'], $handler['authentication_password']);
            break;
            
            case 'token':
                $request->withToken($handler['authentication_token']);
            break;
        }
            
        // timeout
        if ($timeout = $handler['timeout']) {
            $request->timeout($timeout);
        }
        
        // retries
        if ($retries = $handler['retry_count']) {
            $request->retry($retries, $handler['retry_wait']);
        }
            
        // payload?
        if ($handler['payload']) {

            if ($handler['payload_antlers_parse'] ?? false) {
                $payload = Antlers::parse($handler['payload'], array_merge([
                        'trigger_event' => $event, 
                    ], $data));
            }
            
            if ($handler['payload_json_decode'] ?? false) {
                $payload = json_decode($payload, true);
            }
                
            $request->withBody($payload, $handler['payload_content_type']);
        }
        
        // run the request
        $response = $request->{$handler['method']}($handler['url']);
        
        // if we have a response handler class specified then hand off to it
        if (($class = $handler['response_handler']) && class_exists($class)) {
            (new $class())->handle($config, $event, $data, $response);
        }
    }
    
    public function blueprintFields(): array
    {
        return [];
    }
    
    public function title(): string
    {
        return __('Webhook');    
    }
}
