<?php

namespace Amsgames\LaravelShop\Gateways;

/**
 * This file is part of LaravelShop,
 * A shop solution for Laravel.
 *
 * @author Alejandro Mostajo
 * @copyright Amsgames, LLC
 * @license MIT
 * @package Amsgames\LaravelShop
 */

use Amsgames\LaravelShop\Gateways\GatewayPass;

class GatewayCallback extends GatewayPass
{
    /**
     * For callback uses.
     */
    protected $didCallback = false;

    /**
     * Called by shop to charge order's amount.
     *
     * @param Order $order Order.
     *
     * @return bool
     */
    public function onCharge($order)
    {
        $this->statusCode   = 'pending';
        $this->detail       = 'pending response, token:' .  $this->token;
        return parent::onCharge($order);
    }

    /**
     * Called on callback.
     *
     * @param Order $order Order.
     * @param mixed $data  Request input from callback.
     *
     * @return bool
     */
    public function onCallbackSuccess($order, $data = null)
    {
        $this->statusCode   = 'completed';
        $this->detail       = 'success callback';
        $this->didCallback  = true;
    }

    /**
     * Called on callback.
     *
     * @param Order $order Order.
     * @param mixed $data  Request input from callback.
     *
     * @return bool
     */
    public function onCallbackFail($order, $data = null)
    {
        $this->statusCode   = 'failed';
        $this->detail       = 'failed callback';
        $this->didCallback  = true;
    }

    /**
     * Returns successful callback URL
     * @return false
     */
    public function getCallbackSuccess()
    {
        return $this->callbackSuccess;
    }

    /**
     * Returns fail callback URL
     * @return false
     */
    public function getCallbackFail()
    {
        return $this->callbackFail;
    }

    /**
     * Returns successful callback URL
     * @return false
     */
    public function getDidCallback()
    {
        return $this->didCallback;
    }
}