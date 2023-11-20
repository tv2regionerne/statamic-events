<?php

uses(\Tv2regionerne\StatamicEvents\Tests\TestCase::class);
use Illuminate\Support\Facades\Event;
use Statamic\Facades;
use Tv2regionerne\StatamicEvents\Facades\Drivers;
use Tv2regionerne\StatamicEvents\Models\Handler;


it('intercepts a defined event', function () {
    $handler = Handler::factory()->make();
    $handler->event = 'Statamic\Events\EntrySaving';
    $handler->driver = 'tester';
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Drivers::shouldReceive('all')
        ->once()
        ->andReturn(collect());

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();
});

it('doesnt intercept an undefined event', function () {
    $handler = Handler::factory()->make();
    $handler->event = 'Statamic\Events\TermSaved';
    $handler->driver = 'tester';
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Drivers::shouldReceive('all')
        ->never()
        ->andReturn(collect());

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();
});
