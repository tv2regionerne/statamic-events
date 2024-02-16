<?php

namespace Tv2regionerne\StatamicEvents\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController as StatamicController;
use Tv2regionerne\StatamicEvents\Events\TriggerEvent;

class TriggerController extends StatamicController
{
    public function index(Request $request)
    {
        if (! $id = $request->input('entry_id')) {
            abort(404);
        }

        if (! $entry = Entry::find($id)) {
            abort(404);
        }

        TriggerEvent::dispatch($entry);
    }
}
