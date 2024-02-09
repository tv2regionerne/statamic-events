<?php

namespace Tv2regionerne\StatamicEvents\Events;

use Statamic\Events\Event;

class HandlerFailed extends Event
{
    public function __construct(public $handler, public $execution, public $error)
    {
    }
}
