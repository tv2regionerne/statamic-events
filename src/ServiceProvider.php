<?php

namespace Tv2regionerne\StatamicEvents;

use Illuminate\Support\Facades\Route;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Tv2regionerne\StatamicEvents\Facades\Drivers;
use Tv2regionerne\StatamicEvents\Http\Controllers\Api\HandlerController;
use Tv2regionerne\StatamicEvents\Listeners\EventSubscriber;
use Tv2regionerne\StatamicPrivateApi\Facades\PrivateApi;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        Actions\DeleteHandler::class,
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $scopes = [
        Scopes\Date::class,
        Scopes\Handler::class,
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

    public function boot()
    {
        parent::boot();

        $this->bootApi();
    }

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
            $nav->create(__('Handlers'))
                ->section(__('Statamic Events'))
                ->icon('time')
                ->route('statamic-events.handlers.index');

            $nav->create(__('Executions'))
                ->section(__('Statamic Events'))
                ->icon('code')
                ->route('statamic-events.executions.index');
        });

        return $this;
    }

    private function bootPermissions()
    {
        Permission::register('view statamic events', function ($permission) {
            $permission
                ->label(__('View Event Handlers'))
                ->children([
                    Permission::make('edit statamic events')
                        ->label(__('Edit Event Handlers'))
                        ->children([
                            Permission::make('create statamic events')
                                ->label(__('Create Event Handlers')),

                            Permission::make('delete statamic events')
                                ->label(__('Delete Event Handlers')),
                        ]),
                ]);
        })->group('Statamic Events');

        return $this;
    }

    private function bootApi()
    {
        if (class_exists(PrivateApi::class)) {
            PrivateApi::addRoute(function () {
                Route::prefix('/statamic-events/handlers')
                    ->group(function () {
                        Route::get('/', [HandlerController::class, 'index']);
                        Route::get('{id}', [HandlerController::class, 'show']);
                        Route::post('/', [HandlerController::class, 'store']);
                        Route::patch('{id}', [HandlerController::class, 'update']);
                        Route::delete('{id}', [HandlerController::class, 'destroy']);
                    });
            });
        }

        return $this;
    }
}
