<?php

uses(\Tv2regionerne\StatamicEvents\Tests\TestCase::class);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Statamic\Facades;
use Tv2regionerne\StatamicEvents\Models\Handler;

it('it creates a get request', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'webhook';
    $handler->config = [
        'url' => 'http://www.tv2.com',
        'method' => 'get',
    ];
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Http::fake();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'http://www.tv2.com' && $request->method() == 'GET';
    });
});

it('it does nothing when theres no url', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'webhook';
    $handler->config = [
        'method' => 'get',
    ];
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Http::fake();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Http::assertNotSent(function (Request $request) {
        return $request;
    });
});

it('it adds headers to a request', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'webhook';
    $handler->config = [
        'url' => 'http://www.tv2.com',
        'method' => 'get',
        'headers' => [
            [
                'key' => 'test',
                'value' => 'test',
            ],
        ],
    ];
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Http::fake();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Http::assertSent(function (Request $request) {
        return collect($request->headers())->has('test');
    });
});

it('it adds basic authentication to a request', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'webhook';
    $handler->config = [
        'url' => 'http://www.tv2.com',
        'method' => 'get',
        'authentication_type' => 'basic',
        'authentication_user' => 'test',
        'authentication_password' => 'test',
    ];
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Http::fake();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Http::assertSent(function (Request $request) {
        $headers = collect($request->headers());

        return $headers->has('Authorization') && str_contains($headers->get('Authorization')[0], 'Basic');
    });
});

it('it adds bearer token authentication to a request', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'webhook';
    $handler->config = [
        'url' => 'https://www.tv2.com',
        'method' => 'get',
        'authentication_type' => 'token',
        'authentication_token' => 'test',
    ];
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Http::fake();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Http::assertSent(function (Request $request) {
        $headers = collect($request->headers());

        return $headers->has('Authorization') && str_contains($headers->get('Authorization')[0], 'Bearer');
    });
});

it('it adds a payload to a post request', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'webhook';
    $handler->config = [
        'url' => 'https://www.tv2.com',
        'method' => 'post',
        'payload' => 'i am a payload',
        'payload_content_type' => 'text/plain',
    ];
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Http::fake();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Http::assertSent(function (Request $request) {
        $headers = collect($request->headers());

        return $request->method() == 'POST' &&
            $headers->has('Content-Type') &&
            str_contains($headers->get('Content-Type')[0], 'text/plain') &&
            $headers->has('Content-Length') &&
            str_contains($headers->get('Content-Length')[0], '14');
    });
});

it('it calls a response_handler handler', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'webhook';
    $handler->config = [
        'url' => 'http://www.tv2.com',
        'method' => 'get',
        'response_handler' => TestResponseHandler::class,
    ];
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Http::fake();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    // we would see an error here if the response handler had not been called
});

class WebhookDriverTest
{
    public function handle($config, $eventName, $event, $response)
    {
        return true;
    }
}
