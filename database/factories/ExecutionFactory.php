<?php

namespace Database\Factories\Tv2regionerne\StatamicEvents\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tv2regionerne\StatamicEvents\Models\Handler;

class ExecutionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Execution::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'event' => $this->faker->name(),
            'input' => $this->faker->name(),
            'output' => $this->faker->name(),
            'status' => 'pending',
        ];
    }
}
