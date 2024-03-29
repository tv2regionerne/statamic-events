<?php

uses(\Tv2regionerne\StatamicEvents\Tests\TestCase::class);
use Statamic\Facades\User;
use Tv2regionerne\StatamicEvents\Models\Handler;

test('get model index', function () {
    Handler::factory()->count(2)->create();
    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->get(cp_route('statamic-events.handlers.index'))
        ->assertOk()
        ->assertViewIs('statamic-events::handlers.index')
        ->assertSee([
            'listing-config',
            'columns',
        ]);
});

test('can create record', function () {
    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->get(cp_route('statamic-events.handlers.create'))
        ->assertOk();
});

test('can store record', function () {
    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->post(cp_route('statamic-events.handlers.store'), [
            'title' => 'Testing handler',
            'events' => ['Some\\Event\\Name'],
            'should_queue' => false,
            'throw_exception_on_fail' => false,
            'enabled' => true,
            'driver' => 'audit',
            'level' => 'info',
            'message' => 'xxx',
        ])
        ->assertOk()
        ->assertJsonStructure([
            'redirect',
        ]);

    $this->assertDatabaseHas('event_handlers', [
        'title' => 'Testing handler',
    ]);
});

test('can edit record', function () {
    $handler = Handler::factory()->create();
    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->get(cp_route('statamic-events.handlers.edit', ['record' => $handler->id]))
        ->assertOk()
        ->assertSee($handler->title);
});

test('cant edit record when it does not exist', function () {
    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->get(cp_route('statamic-events.handlers.edit', ['record' => 12345]))
        ->assertNotFound()
        ->assertSee('Page Not Found');
});

test('can update resource', function () {
    $handler = Handler::factory()->create();
    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->patch(cp_route('statamic-events.handlers.update', ['record' => $handler->id]), [
            'title' => 'Changed handler',
            'events' => ['Some\\Event\\Name'],
            'driver' => 'audit',
            'should_queue' => false,
            'throw_exception_on_fail' => false,
            'enabled' => true,
            'level' => 'info',
            'message' => 'xxx',
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data',
        ]);

    $handler->refresh();

    expect('Changed handler')->toEqual($handler->title);
});
