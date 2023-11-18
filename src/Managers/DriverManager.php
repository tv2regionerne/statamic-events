<?php

namespace Tv2regionerne\StatamicEvents\Managers;

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
    
    public function add(string $handle, string $classname): self
    {
        $this->drivers->put($handle, $classname);

        return $this;
    }
    
    public function all(): Collection
    {
        return collect($this->drivers)->map(function ($class) {
            return app($class);
        });
    }
    
    public function remove(string $handle): self
    {
        $this->drivers->remove($handle);

        return $this;
    }
}
