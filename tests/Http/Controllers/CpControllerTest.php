<?php

namespace Tv2regionerne\StatamicEvents\Tests\Http\Controllers;

use Statamic\Facades\Config;
use Statamic\Facades\User;
use Tv2regionerne\StatamicEvents\Models\Handler;
use Tv2regionerne\StatamicEvents\Tests\TestCase;

class CpControllerTest extends TestCase
{
    /** @test */
    public function get_model_index()
    {
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
    }

    /** @test */
    public function can_create_record()
    {
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('statamic-events.create'))
            ->assertOk();
    }

    /** @test */
    public function can_store_record()
    {
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
    }

    /** @test */
    public function can_edit_record()
    {
        $handler = Handler::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('statamic-events.edit', ['record' => $handler->id]))
            ->assertOk()
            ->assertSee($handler->title);
    }

    /** @test */
    public function cant_edit_record_when_it_does_not_exist()
    {
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('statamic-events.edit', ['record' => 12345]))
            ->assertNotFound()
            ->assertSee('Page Not Found');
    }

    /** @test */
    public function can_update_resource()
    {
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

        $this->assertEquals($handler->title, 'Changed handler');
    }
}
