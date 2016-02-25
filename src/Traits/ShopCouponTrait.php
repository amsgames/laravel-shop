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

    /**
     * Scopes to get coupons for cart.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereCart($query, $cartId)
    {
        $ccTable = Config::get('shop.cart_coupon_table');
        return $query->join($ccTable, $ccTable . '.coupon_id', '=', Config::get('shop.coupon_table') . '.id')
            ->where($ccTable . '.cart_id', $cartId);
    }
}