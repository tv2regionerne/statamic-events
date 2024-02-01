<?php

namespace Tv2regionerne\StatamicEvents\ResponseHandlers;

use Illuminate\Http\Response;
use Statamic\Facades;

class DefaultResponseHandler
{
    public function handle(array $config, string $eventName, mixed $event, mixed $response = null)
    {
        if (! $response) {
            return;
        }

        if ($response instanceof Response) {
            if (! $response->ok()) {
                return;
            }

            $response = $response->json();
        }

        if ($item = $event->asset) {
            Facades\Asset::find($item->id())
                ->data($response)
                ->saveQuietly();

            return;
        }

        if ($item = $event->entry) {
            Facades\Entry::find($item->id())
                ->data($response)
                ->saveQuietly();

            return;
        }

        if ($item = $event->term) {
            Facades\Term::find($item->id())
                ->data($response)
                ->saveQuietly();

            return;
        }
    }
}
