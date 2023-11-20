<?php

uses(\Tv2regionerne\StatamicEvents\Tests\TestCase::class);
use Statamic\Facades\Config;
use Statamic\Facades\User;
use Tv2regionerne\StatamicEvents\Models\Handler;

test('get model index', function () {
    Handler::factory()->count(2)->create();
    $user = User::make()->makeSuper()->save();

    $response = $this
        ->actingAs($user)
        ->get(cp_route('statamic-events.index'))
        ->assertOk()
        ->assertViewIs('statamic-events::index')
        ->assertSee([
            'listing-config',
            'columns',
        ]);
});

test('can create record', function () {
    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->get(cp_route('statamic-events.create'))
        ->assertOk();
});

test('can store record', function () {
    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->post(cp_route('statamic-events.store'), [
            'title' => 'Testing handler',
            'event' => 'Some\\Event\\Name',
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
        ->get(cp_route('statamic-events.edit', ['record' => $handler->id]))
        ->assertOk()
        ->assertSee($handler->title);
});

test('cant edit record when it does not exist', function () {
    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->get(cp_route('statamic-events.edit', ['record' => 12345]))
        ->assertNotFound()
        ->assertSee('Page Not Found');
});

test('can update resource', function () {
    $handler = Handler::factory()->create();
    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->patch(cp_route('statamic-events.update', ['record' => $handler->id]), [
            'title' => 'Changed handler',
            'event' => 'Some\\Event\\Name',
            'driver' => 'audit',
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
