<?php

namespace Tv2regionerne\StatamicEvents\Drivers;

abstract class AbstractDriver
{
    abstract public function handle(array $config, string $eventName, $event): void;

    abstract public function blueprintFields(): array;

    abstract public function title(): string;
}
