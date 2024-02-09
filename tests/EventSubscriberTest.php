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

it('doesnt intercept a defined event when filter fails', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'webhook';
    $handler->enabled = true;
    $handler->filter = '{{ if true }}false{{ else }}true{{ /if }}';
    $handler->title = 'ryan';
    $handler->save();

    Facades\Blink::forget('statamic-events::handlers::all');

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    $this->assertSame('failed', $handler->executions->first()->status);
});

it('intercepts a defined event when filter passes', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'webhook';
    $handler->filter = 'true';
    $handler->save();

    Facades\Blink::forget('statamic-events::handlers::all');

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    $this->assertCount(0, $handler->executions->first()->logs()->get()->where('description', __('Failed to pass filter')));
});

it('fires handler failed when a filter is invalid', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'webhook';
    $handler->filter = '{{{ if true }}false{{ else }}true{{ /if }}';
    $handler->save();

    Facades\Blink::forget('statamic-events::handlers::all');

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    // register the listener
    Event::fake([\Tv2regionerne\StatamicEvents\Events\HandlerFailed::class]);

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Event::assertDispatched(\Tv2regionerne\StatamicEvents\Events\HandlerFailed::class);
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
