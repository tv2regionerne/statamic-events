<?php

uses(\Tv2regionerne\StatamicEvents\Tests\TestCase::class);

use Illuminate\Support\Facades\Log;
use Statamic\Facades;
use Tv2regionerne\StatamicEvents\Models\Handler;

it('logs a message', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'audit';
    $handler->config = [
        'level' => 'info',
        'message' => 'testing info logging',
    ];
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Log::spy();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Log::shouldHaveReceived('info')->once()->with('testing info logging');
});

it('logs a message parsed with antlers', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'audit';
    $handler->config = [
        'level' => 'info',
        'message' => 'testing info logging: {{ entry:slug }}',
    ];
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Log::spy();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Log::shouldHaveReceived('info')->once()->with('testing info logging: test');
});

it('logs a warning message', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'audit';
    $handler->config = [
        'level' => 'warning',
        'message' => 'testing info logging',
    ];
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Log::spy();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Log::shouldHaveReceived('warning')->once()->with('testing info logging');
});
