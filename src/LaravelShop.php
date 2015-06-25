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
use Amsgames\LaravelShop\Exceptions\CheckoutException;
use Amsgames\LaravelShop\Exceptions\GatewayException;
use Amsgames\LaravelShop\Exceptions\ShopException;

class LaravelShop
{
    /**
     * Forces quantity to reset when adding items to cart.
     * @var bool
     */
    const QUANTITY_RESET                = true;

    /**
     * Gateway in use.
     * @var string
     */
    protected static $gatewayKey        = null;

    /**
     * Gateway instance.
     * @var object
     */
    protected static $gateway           = null;

    /**
     * Gatway in use.
     * @var string
     */
    protected static $exception         = null;

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
        $this->app              = $app;
        static::$gatewayKey     = $this->getGateway();
        static::$exception      = null;
        static::$gateway        = static::instanceGateway();
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
    public static function setGateway($gatewayKey)
    {
        if (!array_key_exists($gatewayKey, Config::get('shop.gateways')))
            throw new ShopException('Invalid gateway.');
        static::$gatewayKey = $gatewayKey;
        static::$gateway    = static::instanceGateway();
        Session::push('shop.gateway', $gatewayKey);
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
     *
     * @return bool
     */
    public static function checkout($cart = null)
    {
        try {
            if (empty(static::$gatewayKey)) {
                throw new ShopException('Payment gateway not selected.');
            }
            if (empty($cart)) $cart = Auth::user()->cart;
            static::$gateway->onCheckout($cart);
        } catch (ShopException $e) {
            static::setException($e);
            return false;
        } catch (CheckoutException $e) {
            static::$exception = $e;
            return false;
        } catch (GatewayException $e) {
            static::$exception = $e;
            return false;
        }
        return true;
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
        try {
            if (empty(static::$gatewayKey))
                throw new ShopException('Payment gateway not selected.');
            if (empty($cart)) $cart = Auth::user()->cart;
            $order = $cart->placeOrder();
            if (static::$gateway->onCharge($order)) {
                $order->statusCode = 'completed';
                $order->save();
                // Create transaction
                $order->placeTransaction(
                    static::$gatewayKey,
                    static::$gateway->getTransactionId(),
                    static::$gateway->getTransactionDetail()
                );
            } else {
                $order->statusCode = 'failed';
                $order->save();
            }
        } catch (ShopException $e) {
            static::setException($e);
            if (isset($order)) {
                $order->statusCode = 'failed';
                $order->save();
            }
        } catch (GatewayException $e) {
            static::$exception = $e;
            if (isset($order)) {
                $order->statusCode = 'failed';
                $order->save();
            }
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
        return static::$gateway;
    }

    /**
     * Retuns exception.
     *
     * @return Exception
     */
    public static function exception()
    {
        return static::$exception;
    }

    /**
     * Saves exception in class.
     *
     * @param mixed $e Exception
     */
    protected static function setException($e)
    {
        Log::error($e);
        static::$exception = $e;
    }

    /**
     * Retunes gateway object.
     * @return object 
     */
    protected static function instanceGateway()
    {
        if (empty(static::$gatewayKey)) return;
        $className = '\\' . Config::get('shop.gateways')[static::$gatewayKey];
        return new $className(static::$gatewayKey);
    }
}
