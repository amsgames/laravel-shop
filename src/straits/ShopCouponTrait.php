<?php

namespace Amsgames\LaravelShop\Traits;

/**
 * This file is part of LaravelShop,
 * A shop solution for Laravel.
 *
 * @author Alejandro Mostajo
 * @copyright Amsgames, LLC
 * @license MIT
 * @package Amsgames\LaravelShop
 */

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

trait ShopCouponTrait
{

    /**
     * Scopes class by coupon code.
     *
     * @return QueryBuilder
     */
    public function scopeWhereCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scopes class by coupen code and returns object.
     *
     * @return this
     */
    public function scopeFindByCode($query, $code)
    {
        return $query->where('code', $code)->first();
    }

}