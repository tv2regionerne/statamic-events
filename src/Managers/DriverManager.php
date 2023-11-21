<?php

namespace Tv2regionerne\StatamicEvents\Managers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class DriverManager
{
    private ?Collection $drivers;

    /**
     * Instantiate the class.
     */
    public function __construct()
    {
        $this->drivers = collect();
    }

    public function add(string $handle, array $config): self
    {
        if (($driver = Arr::get($config, 'driver')) && class_exists($driver)) {
            $this->drivers->put($handle, $config);
        }

        return $this;
    }

    public function all(): Collection
    {
        return collect($this->drivers)->map(function ($config) {
            $driver = $config['driver'];
            unset($config['driver']);

            return app($driver)->withConfig($config);
        });
    }

    public function remove(string $handle): self
    {
        $this->drivers->forget($handle);

        return $this;
    }
}
