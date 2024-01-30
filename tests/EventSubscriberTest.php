<?php

uses(\Tv2regionerne\StatamicEvents\Tests\TestCase::class);

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Statamic\Facades;
use Tv2regionerne\StatamicEvents\Facades\Drivers;
use Tv2regionerne\StatamicEvents\Jobs\RunHandler;
use Tv2regionerne\StatamicEvents\Models\Handler;


it('intercepts a defined event', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
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
    $handler->events = ['Statamic\Events\TermSaved'];
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

it('intercepts a defined event when disabled', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'tester';
    $handler->enabled = false;
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Drivers::shouldReceive('all')
        ->never();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();
});

it('queues a defined event', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'webhook';
    $handler->should_queue = true;
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Queue::fake();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Queue::assertPushed(RunHandler::class, function ($job) {
        return $job->eventName == 'Statamic\\Events\\EntrySaving';
    });
});

it('doesnt queue a defined event', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'webhook';
    $handler->should_queue = false;
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Queue::fake();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Queue::assertNotPushed(RunHandler::class);
});

it('logs an execution', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'webhook';
    $handler->should_queue = false;
    $handler->save();

    expect($handler->executions)->toHaveCount(0);

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Queue::fake();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Queue::assertNotPushed(RunHandler::class);

    expect($handler->executions)->toHaveCount(0);
});
