<?php

uses(\Tv2regionerne\StatamicEvents\Tests\TestCase::class);
use Illuminate\Support\Collection;
use Tv2regionerne\StatamicEvents\Drivers\AbstractDriver;
use Tv2regionerne\StatamicEvents\Drivers\WebhookDriver;
use Tv2regionerne\StatamicEvents\Facades\Drivers;


test('can discover and get all drivers', function () {
    $all = Drivers::all();

    expect($all instanceof Collection)->toBeTrue();
    expect($all)->toHaveCount(2);

    expect($all->first() instanceof AbstractDriver)->toBeTrue();
});

test('can register a new driver', function () {
    Drivers::add('test_driver', WebhookDriver::class);

    $all = Drivers::all();

    expect($all)->toHaveCount(3);
});

test('can remove an existing driver', function () {
    Drivers::remove('webhook');

    $all = Drivers::all();

    expect($all)->toHaveCount(1);
});
