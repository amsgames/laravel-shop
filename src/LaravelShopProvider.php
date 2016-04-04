<?php

namespace Amsgames\LaravelShop;

/**
 * Service provider for laravel.
 *
 * @author Alejandro Mostajo
 * @copyright Amsgames, LLC
 * @license MIT
 * @package Amsgames\LaravelShop
 */

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class LaravelShopProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        parent::boot($router);

        // Publish config files
        $this->publishes([
            __DIR__ . '/Config/config.php' => config_path('shop.php'),
        ]);

        // Register commands
        $this->commands('command.laravel-shop.migration');

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerShop();

        $this->registerCommands();

        $this->mergeConfig();
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    private function registerShop()
    {
        $this->app->singleton('shop', function ($app) {
            return new LaravelShop($app);
        });
    }

    /**
     * Merges user's and entrust's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/config.php', 'shop'
        );
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->app->singleton('command.laravel-shop.migration', function ($app) {
            return new MigrationCommand();
        });
    }

    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'shop', 'command.laravel-shop.migration'
        ];
    }

    /**
     * Maps router.
     * Add package special controllers.
     *
     * @param Router $route Router.
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => 'Amsgames\LaravelShop\Http\Controllers'], function($router) {

            $router->group(['prefix' => 'shop'], function ($router) {

                $router->get('callback/payment/{status}/{id}/{shoptoken}', ['as' => 'shop.callback', 'uses' => 'Shop\CallbackController@process']);

                $router->post('callback/payment/{status}/{id}/{shoptoken}', ['as' => 'shop.callback', 'uses' => 'Shop\CallbackController@process']);

            });

        });
    }
	
}