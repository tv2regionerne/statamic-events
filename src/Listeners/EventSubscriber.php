<?php

namespace Tv2regionerne\StatamicEvents\Listeners;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Statamic\Eloquent\Entries\Entry;

class EventSubscriber
{

    /**
     * Create the event listener.
     */
    public function __construct()
    {

    }

    public function subscribe($dispatcher)
    {
        $dispatcher->listen('*', self::class.'@handleEvent');
    }

    public function handleEvent($event, $data)
    {

        if (!class_exists($event)) {
            return;
        }

        if ($eventHandlers = data_get(config('statamic-events.events'), $event)) {
            foreach ($eventHandlers as $eventHandler) {
                switch ($eventHandler['type']) {
                    case 'webhook':
                        $entry = $data[0]->entry;
                        $data = $this->callWebhook($eventHandler, $event, $data);
                        $eventHandler['handler']($entry, $data);
                        break;
                }
            }
        }
    }

    public function callWebhook($eventHandler, $event, $data)
    {
        /** @var Entry $entry */
        $entry = $data[0]->entry;

        $input = $eventHandler['payload']($entry);

        $repsonse = Http::post($eventHandler['endpoint'], $input);
        return $repsonse->collect();
    }
}
