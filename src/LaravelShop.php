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

use Auth;
use Amsgames\LaravelShop\Gateways;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class LaravelShop
{
    /**
     * Forces quantity to reset when adding items to cart.
     * @var bool
     */
    const QUANTITY_RESET            = true;

    /**
     * Gatway in use.
     * @var string
     */
    protected static $gateway       = null;

    /**
     * Laravel application
     *
     * @var \Illuminate\Foundation\Application
     */
    private $errorMessage;

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
        static::$gateway = $this->getGateway();
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
     * Checkout current user's cart.
     */
    public static function setGateway($gateway)
    {
        if (!array_key_exists($gateway, Config::get('shop.gateways')))
            throw new Exception('Invalid gateway.');
        static::$gateway = $gateway;
        Session::push('shop.gateway', $gateway);
    }

    /**
     * Checkout current user's cart.
     */
    public static function getGateway()
    {
        return Session::get('shop.gateway')[0]; 
    }

    /**
     * Checkout current user's cart.
     *
     * @param object $cart For specific cart.
     */
    public static function checkout($cart = null)
    {
        if (empty(static::$gateway))
            throw new Exception('Payment gateway not selected.');
        if (empty($cart)) $cart = Auth::user()->cart;
        $gateway = static::instanceGateway();
        $gateway->onCheckout($cart);
    }

    /**
     * Returns placed order.
     *
     * @param object $cart For specific cart.
     *
     * @return object
     */
    public static function placeOrder($cart = null)
    {
        if (empty(static::$gateway))
            throw new Exception('Payment gateway not selected.');
        if (empty($cart)) $cart = Auth::user()->cart;
        $order = $cart->placeOrder();
        $gateway = static::instanceGateway();
        if ($gateway->onCharge($order)) {
            $order->statusCode = 'completed';
            $order->save();
            // Create transaction
            $order->placeTransaction(
                static::$gateway,
                $gateway->getTransactionId(),
                $gateway->getTransactionDetail()
            );
        } else {
            $order->statusCode = 'failed';
            $order->save();
        }
        return $order;
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

    /**
     * Retuns gateway.
     *
     * @return object
     */
    public static function gateway()
    {
        return static::instanceGateway();
    }

    /**
     * Retunes gateway object.
     * @return object 
     */
    protected static function instanceGateway()
    {
        if (empty(static::$gateway)) return;
        $className = '\\' . Config::get('shop.gateways')[static::$gateway];
        return new $className(static::$gateway);
    }
}
