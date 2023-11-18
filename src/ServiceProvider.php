<?php

namespace Tv2regionerne\StatamicEvents;

use Statamic\Providers\AddonServiceProvider;
use Tv2regionerne\StatamicEvents\Facades\Drivers;
use Tv2regionerne\StatamicEvents\Listeners\EventSubscriber;

class ServiceProvider extends AddonServiceProvider
{
    protected $subscribe = [
        EventSubscriber::class,
    ];

    public function bootAddon()
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/config/statamic-events.php', 'statamic-events');
        
        $this->publishes([
            dirname(__DIR__) => config_path('statamic-events.php'),
        ], 'statamic-events-config');
        
        $this->bootDrivers();
    }
    
    private function bootDrivers()
    {
        collect(config('statamic-events.drivers', []))
            ->each(fn ($class, $handle) => Drivers::add($handle, $class));
                
        return $this;
    }
}
