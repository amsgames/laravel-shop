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

use Log;
use Amsgames\LaravelShop\Core\PaymentGateway;

class GatewayPass extends PaymentGateway
{
    /**
     * Called by shop to charge order's amount.
     *
     * @param Order $order Order.
     *
     * @return bool
     */
    public function onCharge($order)
    {
        $this->transactionId = uniqid();
    	return true;
    }
}