<?php

uses(\Tv2regionerne\StatamicEvents\Tests\TestCase::class);

use Illuminate\Support\Facades\Mail;
use Statamic\Facades;
use Tv2regionerne\StatamicEvents\Mail\PlainMail;
use Tv2regionerne\StatamicEvents\Models\Handler;

it('creates a plain text mail', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'email';
    $handler->config = [
        'to' => ['test@test.com'],
        'text' => 'This is some text',
    ];
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Mail::fake();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Mail::assertSentCount(1);

    Mail::assertSent(PlainMail::class, function (PlainMail $mail) {
        return $mail->config['plain'] == 'This is some text';
    });
});

it('creates an html mail', function () {
    $handler = Handler::factory()->make();
    $handler->events = ['Statamic\Events\EntrySaving'];
    $handler->driver = 'email';
    $handler->config = [
        'to' => ['test@test.com'],
        'html' => '<p>This is some text</p>',
    ];
    $handler->save();

    // register the listener
    Event::listen(
        \Statamic\Events\EntrySaving::class,
        [\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class, 'handleEvent']
    );

    Mail::fake();

    Facades\Blink::forget('statamic-events::handlers::all');

    $collection = Facades\Collection::make('test');
    $collection->save();

    $entry = Facades\Entry::make();
    $entry->collection($collection);
    $entry->slug('test');
    $entry->id('test');
    $entry->save();

    Mail::assertSentCount(1);

    Mail::assertSent(PlainMail::class, function (PlainMail $mail) {
        return $mail->config['html'] == '<p>This is some text</p>';
    });
});
