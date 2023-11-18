<?php

namespace Tv2regionerne\StatamicEvents\Listeners;

use Statamic\Facades\Blink;
use Tv2regionerne\StatamicEvents\Facades\Drivers;
use Tv2regionerne\StatamicEvents\Models\Handler;

class EventSubscriber
{
    public function subscribe($dispatcher)
    {
        // only listen for the events we actually need, to avoid memory or return value issues
        $this->getHandlers()
            ->groupBy('event')
            ->each(function ($handler, $event) use ($dispatcher) {
                if (! class_exists($event)) {
                    return;
                }

                $dispatcher->listen($event, self::class.'@handleEvent');
            });
    }

    public function handleEvent($event, $params)
    {        
        $this->getHandlers()
            ->where('event', $event)
            ->each(function ($handler) use ($event, $params) {
                if ($driver = Drivers::all()->get($handler->driver)) {
                    $driver->handle($handler->config, $params);
                }
            });
    }
    
    private function getHandlers()
    {
        return Blink::once('statamic-events-handlers::all', fn () => Handler::all());
    }
}
