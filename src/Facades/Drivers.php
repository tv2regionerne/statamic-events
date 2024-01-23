<?php

namespace Tv2regionerne\StatamicEvents\Facades;

use Illuminate\Support\Facades\Facade;
use Tv2regionerne\StatamicEvents\Managers\DriverManager;

class Drivers extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return DriverManager::class;
    }
}
