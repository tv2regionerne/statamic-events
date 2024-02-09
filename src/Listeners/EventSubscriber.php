<?php

namespace Tv2regionerne\StatamicEvents\Listeners;

use Illuminate\Events\Dispatcher;
use Statamic\Facades\Antlers;
use Statamic\Facades\Blink;
use Tv2regionerne\StatamicEvents\Events\HandlerFailed;
use Tv2regionerne\StatamicEvents\Facades\Drivers;
use Tv2regionerne\StatamicEvents\Jobs\RunHandler;
use Tv2regionerne\StatamicEvents\Models\Handler;

class EventSubscriber
{
    public function subscribe(Dispatcher $dispatcher)
    {
        // only listen for the events we actually need, to avoid memory or return value issues
        return $this->getHandlers()
            ->mapWithKeys(function ($handler) {
                return collect($handler->events)
                    ->mapWithKeys(function ($event) {
                        if (! class_exists($event)) {
                            return [];
                        }

                        return [
                            $event => 'handleEvent',
                        ];
                    })
                    ->filter();
            })
            ->all();
    }

    public function handleEvent($event)
    {
        $eventName = get_class($event);

        $this->getHandlers()
            ->filter(fn ($handler) => in_array($eventName, $handler->events) && $handler->enabled)
            ->each(function ($handler) use ($eventName, $event) {
                if ($driver = Drivers::all()->get($handler->driver)) {
                    $execution = $handler->executions()->create([
                        'event' => $eventName,
                        'input' => $event,
                        'status' => 'processing',
                    ]);

                    if ($handler->filter) {
                        try {
                            $result = (string) Antlers::parse($handler->filter, [
                                'eventName' => $eventName,
                                'event' => $event,
                            ]);

                            if (in_array($result, ['0', 'false'])) {
                                $execution->fail(__('Failed to pass filter'));

                                return;
                            }
                        } catch (\Throwable $e) {
                            $execution->fail(__('Error running filter'));

                            HandlerFailed::dispatch($handler, $execution, $e);

                            return;
                        }
                    }

                    if ($handler->should_queue) {
                        $execution->log(__('Added to queue'));

                        RunHandler::dispatch($driver, $handler->config, $eventName, $event, $execution)
                            ->onQueue(config('statamic-events.queue_name', 'default'));

                        return;
                    }

                    $execution->log(__('Processing'));

                    $driver->handle($handler->config, $eventName, $event, $execution);
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
