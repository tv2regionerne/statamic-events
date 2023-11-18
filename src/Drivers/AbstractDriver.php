<?php

namespace Tv2regionerne\StatamicEvents\Drivers;

abstract class AbstractDriver
{
    public function handle(array $config, string $event, array $data): void;
        
    public function blueprintFields(): array;
}