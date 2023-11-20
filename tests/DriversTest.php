<?php

namespace Tv2regionerne\StatamicEvents\Tests;

use Illuminate\Support\Collection;
use Tv2regionerne\StatamicEvents\Drivers\AbstractDriver;
use Tv2regionerne\StatamicEvents\Drivers\WebhookDriver;
use Tv2regionerne\StatamicEvents\Facades\Drivers;

class DriversTest extends TestCase
{
    /** @test */
    public function can_discover_and_get_all_drivers()
    {
        $all = Drivers::all();

        $this->assertTrue($all instanceof Collection);
        $this->assertCount(2, $all);

        $this->assertTrue($all->first() instanceof AbstractDriver);
    }

    /** @test */
    public function can_register_a_new_driver()
    {
        Drivers::add('test_driver', WebhookDriver::class);

        $all = Drivers::all();

        $this->assertCount(3, $all);
    }

    /** @test */
    public function can_remove_an_existing_driver()
    {
        Drivers::remove('webhook');

        $all = Drivers::all();

        $this->assertCount(1, $all);
    }
}
