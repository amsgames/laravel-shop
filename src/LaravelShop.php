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

use Illuminate\Support\Facades\Config;

class LaravelShop
{

    /**
     * Order status in creation.
     * @var string
     */
    const ORDER_IN_CREATION         = 'in_creation';

    /**
     * Order status pending.
     * i.e. Pending for payment.
     * @var string
     */
    const ORDER_PENDING             = 'pending';

    /**
     * Order status in process.
     * i.e. In process of shipping. In process of revision.
     * @var string
     */
    const ORDER_IN_PROCESS          = 'in_process';

    /**
     * Order status completed.
     * i.e. When payment has been made and items were delivered to client.
     * @var string
     */
    const ORDER_COMPLETED           = 'completed';

    /**
     * Order status failed.
     * i.e. When payment failed.
     * @var string
     */
    const ORDER_FAILED              = 'failed';

    /**
     * Order status canceled.
     * i.e. When an order has been canceled by the user.
     * @var string
     */
    const ORDER_CANCELED            = 'canceled';

    /**
     * Forces quantity to reset when adding items to cart.
     * @var bool
     */
    const QUANTITY_RESET            = true;

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
