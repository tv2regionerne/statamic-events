<?php

uses(\Tv2regionerne\StatamicEvents\Tests\TestCase::class);
use Statamic\Facades\User;
use Tv2regionerne\StatamicEvents\Models\Execution;
use Tv2regionerne\StatamicEvents\Models\Handler;

test('get model index', function () {
    $handler = Handler::factory()->create();

    $execution = Execution::factory()->make();
    $execution->handler()->associate($handler);
    $execution->save();

    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->get(cp_route('statamic-events.executions.index'))
        ->assertOk()
        ->assertViewIs('statamic-events::executions.index')
        ->assertSee([
            'listing-config',
            'columns',
        ]);
});

test('get show page', function () {
    $handler = Handler::factory()->create();

    $execution = Execution::factory()->make();
    $execution->handler()->associate($handler);
    $execution->save();

    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->get(cp_route('statamic-events.executions.show', ['record' => $execution->getKey()]))
        ->assertOk()
        ->assertViewIs('statamic-events::executions.show');

    $this
        ->actingAs($user)
        ->get(cp_route('statamic-events.executions.show', ['record' => 2000]))
        ->assertNotFound();
});

