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

use Illuminate\Support\ServiceProvider;

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
    public function boot()
    {
        // Publish config files
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('shop.php'),
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
            __DIR__ . '/config/config.php', 'shop'
        );
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->app->bindShared('command.laravel-shop.migration', function ($app) {
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
	
}