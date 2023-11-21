<?php

namespace Database\Factories\Tv2regionerne\StatamicEvents\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tv2regionerne\StatamicEvents\Models\Handler;

class HandlerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Handler::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->name(),
            'event' => $this->faker->name(),
            'driver' => 'audit',
            'config' => [],
            'enabled' => true,
            'should_queue' => false,
        ];
    }
}
