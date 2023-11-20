<?php

namespace Tv2regionerne\StatamicEvents\Listeners;

use Illuminate\Events\Dispatcher;
use Statamic\Facades\Blink;
use Tv2regionerne\StatamicEvents\Facades\Drivers;
use Tv2regionerne\StatamicEvents\Models\Handler;

class EventSubscriber
{
    public function subscribe(Dispatcher $dispatcher)
    {
        // only listen for the events we actually need, to avoid memory or return value issues
        return $this->getHandlers()
            ->groupBy('event')
            ->mapWithKeys(function ($handler, $event) use ($dispatcher) {
                if (! class_exists($event)) {
                    return [];
                }

                return [
                    $event => 'handleEvent'
                ];
            })
            ->all();
    }

    public function handleEvent($event)
    {
        $eventName = get_class($event);

        $this->getHandlers()
            ->where('event', $eventName)
            ->each(function ($handler) use ($eventName, $event) {
                if ($driver = Drivers::all()->get($handler->driver)) {
                    $driver->handle($handler->config, $eventName, $event);
                }
            });
    }

    private function getHandlers()
    {
        return Blink::once('statamic-events::handlers::all', function () {
            try {
                return Handler::all();
            } catch (\Throwable $e) {
                return collect();
            }
        });
    }
}
