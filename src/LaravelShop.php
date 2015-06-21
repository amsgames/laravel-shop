<?php

namespace Amsgames\LaravelShop;

/**
 * This class is the main entry point of laravel shop. Usually this the interaction
 * with this class will be done through the LaravelShop Facade
 *
 * @author Alejandro Mostajo
 * @copyright Amsgames, LLC
 * @license MIT
 * @package Amsgames\LaravelShop
 */
class LaravelShop
{
    /**
     * Laravel application
     *
     * @var \Illuminate\Foundation\Application
     */
    public $app;

    /**
     * Create a new confide instance.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get the currently authenticated user or null.
     *
     * @return Illuminate\Auth\UserInterface|null
     */
    public function user()
    {
        return $this->app->auth->user();
    }

    /**
     * Formats any value to price format set in config.
     *
     * @param mixed $value Value to format.
     *
     * @return string
     */
    public static function format($value)
    {
        return preg_replace(
            [
                '/:symbol/',
                '/:price/',
                '/:currency/'
            ],
            [
                Config::get('shop.currency_symbol'),
                $value,
                Config::get('shop.currency')
            ],
            Config::get('shop.display_price_format')
        );
    }
}
