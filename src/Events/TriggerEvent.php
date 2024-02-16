<?php

namespace Tv2regionerne\StatamicEvents\Events;

use Statamic\Contracts\Entries\Entry;
use Statamic\Events\Event;

class TriggerEvent extends Event
{
    public function __construct(public Entry $entry)
    {
    }
}
