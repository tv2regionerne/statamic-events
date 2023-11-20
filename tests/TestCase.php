<?php

namespace Tv2regionerne\StatamicEvents\Tests;

use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Stache\Stores\UsersStore;
use Statamic\Statamic;
use Tv2regionerne\StatamicEvents\ServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    use PreventSavingStacheItemsToDisk, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->runLaravelMigrations();

        \Facades\Statamic\Version::shouldReceive('get')->andReturn('4.0.0-testing');
        $this->addToAssertionCount(-1); // Dont want to assert this

        $this->preventSavingStacheItemsToDisk();
    }

    protected function tearDown(): void
    {
        $this->deleteFakeStacheDirectory();

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->make(Manifest::class)->manifest = [
            'tv2regionerne/statamic-events' => [
                'id' => 'tv2regionerne/statamic-events',
                'namespace' => 'Tv2regionerne\\StatamicEvents',
            ]
        ];

       // $app->make(Manifest::class)->build();
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.key', 'base64:'.base64_encode(
            Encrypter::generateKey($app['config']['app.cipher'])
        ));

        $configs = [
            'assets',
            'cp',
            'forms',
            'static_caching',
            'sites',
            'stache',
            'system',
            'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set(
                "statamic.$config",
                require(__DIR__."/../vendor/statamic/cms/config/{$config}.php")
            );
        }

        $app['config']->set('statamic.users.repository', 'file');

        $app['config']->set('statamic.stache.stores.users', [
            'class' => UsersStore::class,
            'directory' => __DIR__.'/__fixtures__/users',
        ]);

        $app['config']->set('statamic-events', require(__DIR__.'/../config/statamic-events.php'));

        $app['config']->set('app.debug', true);

//         Statamic::pushCpRoutes(function () {
//             Route::namespace()->group(__DIR__.'/../routes/cp.php');
//         });
//
//         Event::subscribe(\Tv2regionerne\StatamicEvents\Listeners\EventSubscriber::class);
    }
}
