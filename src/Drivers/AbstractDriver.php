<?php

namespace Tv2regionerne\StatamicEvents\Drivers;

use Illuminate\Support\Arr;
use Tv2regionerne\StatamicEvents\Models\Execution;

abstract class AbstractDriver
{
    protected $config = [];

    abstract public function handle(array $config, string $eventName, $event, Execution $execution): void;

    abstract public function blueprintFields(): array;

    abstract public function title(): string;

    public function withConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    public function get($key, $fallback = null)
    {
        return Arr::get($this->config, $key, $fallback);
    }
}
