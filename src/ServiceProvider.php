<?php

namespace Tv2regionerne\StatamicEvents;

use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Tv2regionerne\StatamicEvents\Facades\Drivers;
use Tv2regionerne\StatamicEvents\Listeners\EventSubscriber;
use Tv2regionerne\StatamicEvents\Managers\DriverManager;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        Actions\DeleteHandler::class,
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $subscribe = [
        EventSubscriber::class,
    ];

    protected $vite = [
        'publicDirectory' => 'dist',
        'input' => [
            'resources/js/cp.js',
        ],
    ];

    public function bootAddon()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'statamic-events');
        $this->mergeConfigFrom(__DIR__.'/../config/statamic-events.php', 'statamic-events');

        $this->publishes([
            __DIR__.'/../config/statamic-events.php' => config_path('statamic/statamic-events.php'),
        ], 'statamic-events-config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->bootDrivers()
            ->bootPermissions()
            ->bootNavigation();
    }

    private function bootDrivers()
    {
        collect(config('statamic-events.drivers', []))
            ->each(fn ($class, $handle) => Drivers::add($handle, $class));

        return $this;
    }

    private function bootNavigation()
    {
        Nav::extend(function ($nav) {
            $nav->create(__('Event Handlers'))
                ->section(__('Utilities'))
                ->icon('time')
                ->route('statamic-events.index')
                ->can('view events');
        });

        return $this;
    }

    private function bootPermissions()
    {
        Permission::register('view statamic events', function ($permission) {
            $permission
                ->label(__('View Event Handlers'))
                ->children([
                    Permission::make("edit statamic events")
                        ->label(__('Edit Event Handlers'))
                        ->children([
                            Permission::make("create statamic events")
                                ->label(__('Create Event Handlers')),

                            Permission::make("delete statamic events")
                                ->label(__('Delete Event Handlers')),
                        ]),
                ]);
        })->group('Statamic Events');

        return $this;
    }
}
