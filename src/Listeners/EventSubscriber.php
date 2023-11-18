<?php

namespace Tv2regionerne\StatamicEvents\Listeners;

use Tv2regionerne\StatamicEvents\Facades\Drivers;

class EventSubscriber
{
    public function subscribe($dispatcher)
    {
        $dispatcher->listen('*', self::class.'@handleEvent');
    }

    public function handleEvent($event, $params)
    {
        if (! class_exists($event)) {
            return;
        }
        
        // get handlers matching this event
        // think about caching - events can be run multiple times per request
        $handlers = ....;
        
        $handlers->each(function ($handler) use ($event, $params) {
            if ($driver = Drivers::all()->get($handler->driver)) {
                $driver->handle($handler, $params);
            }
        });
    }
}
