<?php

namespace Tv2regionerne\StatamicEvents\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunHandler implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public $driver, public $config, public $eventName, public $event, public $execution) {}

    public function handle()
    {
        $this->execution->log(__('Processing'));

        $this->driver->handle($this->config, $this->eventName, $this->event, $this->execution);
    }
}
