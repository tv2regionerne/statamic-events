<?php

namespace Tv2regionerne\StatamicEvents\Drivers;

use Illuminate\Support\Arr;

abstract class AbstractDriver
{
    protected $config = [];

    abstract public function handle(array $config, string $eventName, $event): void;

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
